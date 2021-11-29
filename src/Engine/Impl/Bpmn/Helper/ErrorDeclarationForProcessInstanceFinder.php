<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Helper;

use BpmPlatform\Engine\Impl\Bpmn\Parser\ErrorEventDefinition;
use BpmPlatform\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmScopeInterface
};
use BpmPlatform\Engine\Impl\Pvm\Process\ScopeImpl;
use BpmPlatform\Engine\Impl\Tree\TreeVisitorInterface;

class ErrorDeclarationForProcessInstanceFinder implements TreeVisitorInterface
{
    protected $exception;
    protected $errorCode;
    protected $errorHandlerActivity;
    protected $errorEventDefinition;
    protected $currentActivity;

    public function __construct(\Exception $exception, string $errorCode, PvmActivityInterface $currentActivity)
    {
        $this->exception = $exception;
        $this->errorCode = $errorCode;
        $this->currentActivity = $currentActivity;
    }

    public function visit(PvmScopeInterface $scope): void
    {
        $errorEventDefinitions = $scope->getProperties()->get(BpmnProperties::errorEventDefinitions());
        foreach ($errorEventDefinitions as $errorEventDefinition) {
            $activityHandler = $scope->getProcessDefinition()->findActivity($errorEventDefinition->getHandlerActivityId());
            if (
                (!$this->isReThrowingErrorEventSubprocess($activityHandler)) &&
                (($exception != null && $errorEventDefinition->catchesException($exception)) ||
                ($exception == null && $errorEventDefinition->catchesError($errorCode)))
            ) {
                $errorHandlerActivity = $activityHandler;
                $this->errorEventDefinition = $errorEventDefinition;
                break;
            }
        }
    }

    protected function isReThrowingErrorEventSubprocess(PvmActivityInterface $activityHandler): bool
    {
        return $activityHandler->isAncestorFlowScopeOf($this->currentActivity);
    }

    public function getErrorHandlerActivity(): PvmActivityInterface
    {
        return $this->errorHandlerActivity;
    }

    public function getErrorEventDefinition(): ErrorEventDefinition
    {
        return $this->errorEventDefinition;
    }
}
