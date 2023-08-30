<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Bpmn\Helper\BpmnExceptionHandler;
use Jabe\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ErrorEndEventActivityBehavior extends AbstractBpmnActivityBehavior
{
    protected $errorCode;
    private $errorMessageExpression;

    public function __construct(?string $errorCode, ?ParameterValueProviderInterface $errorMessage)
    {
        parent::__construct();
        $this->errorCode = $errorCode;
        $this->errorMessageExpression = $errorMessage;
    }

    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        $errorMessageValue = $this->errorMessageExpression !== null ? $this->errorMessageExpression->getValue($execution) : null;
        BpmnExceptionHandler::propagateError($this->errorCode, $errorMessageValue, null, $execution);
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setErrorCode(?string $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public function getErrorMessageExpression(): ParameterValueProviderInterface
    {
        return $this->errorMessageExpression;
    }

    public function setErrorMessageExpression(ParameterValueProviderInterface $errorMessage): void
    {
        $this->errorMessageExpression = $errorMessage;
    }
}
