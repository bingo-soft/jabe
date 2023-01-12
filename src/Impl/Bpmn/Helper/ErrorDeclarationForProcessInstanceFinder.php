<?php

namespace Jabe\Impl\Bpmn\Helper;

use Jabe\Impl\Bpmn\Parser\ErrorEventDefinition;
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmScopeInterface
};
use Jabe\Impl\Tree\TreeVisitorInterface;

class ErrorDeclarationForProcessInstanceFinder implements TreeVisitorInterface
{
    protected $exception;
    protected $errorCode;
    protected $errorHandlerActivity;
    protected $errorEventDefinition;
    protected $currentActivity;

    public function __construct(\Exception $exception, ?string $errorCode, ?PvmActivityInterface $currentActivity)
    {
        $this->exception = $exception;
        $this->errorCode = $errorCode;
        $this->currentActivity = $currentActivity;
    }

    public function visit($scope): void
    {
        $errorEventDefinitions = $scope->getProperties()->get(BpmnProperties::errorEventDefinitions());
        foreach ($errorEventDefinitions as $errorEventDefinition) {
            $activityHandler = $scope->getProcessDefinition()->findActivity($errorEventDefinition->getHandlerActivityId());
            if (
                (!$this->isReThrowingErrorEventSubprocess($activityHandler)) &&
                (($this->exception !== null && $errorEventDefinition->catchesException($this->exception)) ||
                ($this->exception === null && $errorEventDefinition->catchesError($this->errorCode)))
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
