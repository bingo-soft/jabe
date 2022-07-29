<?php

namespace Jabe\Engine\Impl\Bpmn\Helper;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\BpmnError;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Bpmn\Behavior\BpmnBehaviorLogger;
use Jabe\Engine\Impl\Bpmn\Parser\ErrorEventDefinition;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;
use Jabe\Engine\Impl\Tree\{
    ActivityExecutionHierarchyWalker,
    ActivityExecutionMappingCollector,
    ActivityExecutionTuple,
    OutputVariablesPropagator,
    ReferenceWalker,
    WalkConditionInterface
};

class BpmnExceptionHandler
{
    //private final static BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    /**
     * Decides how to propagate the exception properly, e.g. as bpmn error or "normal" error.
     * @param execution the current execution
     * @param ex the exception to propagate
     * @throws Exception if no error handler could be found
     */
    public static function propagateException(ActivityExecutionInterface $execution, \Exception $ex): void
    {
        $bpmnError = self::checkIfCauseOfExceptionIsBpmnError($ex);
        if ($bpmnError !== null) {
            self::propagateBpmnError($bpmnError, $execution);
        } else {
            self::propagateExceptionAsError($ex, $execution);
        }
    }

    protected static function propagateExceptionAsError(\Exception $exception, ActivityExecutionInterface $execution): void
    {
        if (self::isProcessEngineExceptionWithoutCause($exception) || self::isTransactionNotActive()) {
            throw $exception;
        } else {
            self::propagateError(null, $exception->getMessage(), $exception, $execution);
        }
    }

    protected static function isTransactionNotActive(): bool
    {
        return !Context::getCommandContext()->getTransactionContext()->isTransactionActive();
    }

    protected static function isProcessEngineExceptionWithoutCause(\Exception $exception): bool
    {
        return $exception instanceof ProcessEngineException && $exception->getCause() === null;
    }

    /**
     * Searches recursively through the exception to see if the exception itself
     * or one of its causes is a BpmnError.
     *
     * @param e
     *          the exception to check
     * @return BpmnError the BpmnError that was the cause of this exception or null if no
     *         BpmnError was found
     */
    protected static function checkIfCauseOfExceptionIsBpmnError(\Throwable $e): BpmnError
    {
        if ($e instanceof BpmnError) {
            return $e;
        }
        if (method_exists($e, 'getCause')) {
            if ($e->getCause() === null) {
                return null;
            }
            self::checkIfCauseOfExceptionIsBpmnError($e->getCause());
        }
        return null;
    }

    public static function propagateBpmnError(BpmnError $error, ActivityExecutionInterface $execution): void
    {
        self::propagateError($error->getCode(), $error->getMessage(), null, $execution);
    }

    public static function propagateError(string $errorCode, string $errorMessage, \Exception $origException, ActivityExecutionInterface $execution): void
    {
        $walker = new ActivityExecutionHierarchyWalker($execution);

        $errorDeclarationFinder = new ErrorDeclarationForProcessInstanceFinder($origException, $errorCode, $execution->getActivity());
        $activityExecutionMappingCollector = new ActivityExecutionMappingCollector($execution);

        $walker->addScopePreVisitor($errorDeclarationFinder);
        $walker->addExecutionPreVisitor($activityExecutionMappingCollector);
        // map variables to super executions in the hierarchy of called process instances
        $walker->addExecutionPreVisitor(new OutputVariablesPropagator());

        try {
            $walker->walkUntil(new class ($errorDeclarationFinder) implements WalkConditionInterface {

                private $errorDeclarationFinder;

                public function __construct(ErrorDeclarationForProcessInstanceFinder $errorDeclarationFinder)
                {
                    $this->errorDeclarationFinder = $errorDeclarationFinder;
                }

                public function isFulfilled($element = null): bool
                {
                    return $this->errorDeclarationFinder->getErrorEventDefinition() !== null || $element === null;
                }
            });
        } catch (\Exception $e) {
            //LOG.errorPropagationException(execution.getActivityInstanceId(), e);

            // separate the exception handling to support a fail-safe error propagation
            throw new ErrorPropagationException($e->getMessage(), $e->getCode(), $e);
        }

        $errorHandlingActivity = $errorDeclarationFinder->getErrorHandlerActivity();

        // process the error
        if ($errorHandlingActivity === null) {
            if ($origException === null) {
                if (Context::getCommandContext()->getProcessEngineConfiguration()->isEnableExceptionsAfterUnhandledBpmnError()) {
                    //throw LOG.missingBoundaryCatchEventError(execution.getActivity().getId(), errorCode);
                } else {
                    //LOG.missingBoundaryCatchEvent(execution.getActivity().getId(), errorCode);
                    $execution->end(true);
                }
            } else {
                // throw original exception
                throw $origException;
            }
        } else {
            $errorDefinition = $errorDeclarationFinder->getErrorEventDefinition();
            $errorHandlingExecution = $activityExecutionMappingCollector->getExecutionForScope($errorHandlingActivity->getEventScope());

            if ($errorDefinition->getErrorCodeVariable() !== null) {
                $errorHandlingExecution->setVariable($errorDefinition->getErrorCodeVariable(), $errorCode);
            }
            if ($errorDefinition->getErrorMessageVariable() !== null) {
                $errorHandlingExecution->setVariable($errorDefinition->getErrorMessageVariable(), $errorMessage);
            }
            $errorHandlingExecution->executeActivity($errorHandlingActivity);
        }
    }
}
