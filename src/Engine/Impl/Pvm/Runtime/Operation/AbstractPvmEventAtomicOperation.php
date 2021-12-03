<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Impl\Bpmn\Helper\{
    BpmnExceptionHandler,
    ErrorPropagationException
};
use BpmPlatform\Engine\Impl\Core\Model\CoreModelElement;
use BpmPlatform\Engine\Impl\Core\Instance\CoreExecution;
use BpmPlatform\Engine\Impl\Core\Operation\AbstractEventAtomicOperation;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

abstract class AbstractPvmEventAtomicOperation extends AbstractEventAtomicOperation implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    abstract protected function getScope(CoreExecution $execution): CoreModelElement;

    public function isAsyncCapable(): bool
    {
        return false;
    }

    protected function eventNotificationsFailed(CoreExecution $execution, \Exception $exception): void
    {

        if ($this->shouldHandleFailureAsBpmnError()) {
            $activityExecution = $execution;
            try {
                $this->resetListeners($execution);
                BpmnExceptionHandler::propagateException($activityExecution, $exception);
            } catch (ErrorPropagationException $e) {
                // exception has been logged by thrower
                // re-throw the original exception so that it is logged
                // and set as cause of the failure
                parent::eventNotificationsFailed($execution, $exception);
            } catch (\Exception $e) {
                parent::eventNotificationsFailed($execution, $e);
            }
        } else {
            parent::eventNotificationsFailed($execution, $exception);
        }
    }

    public function shouldHandleFailureAsBpmnError(): bool
    {
        return false;
    }
}
