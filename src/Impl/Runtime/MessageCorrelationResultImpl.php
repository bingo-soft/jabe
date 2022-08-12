<?php

namespace Jabe\Impl\Runtime;

use Jabe\Runtime\{
    ExecutionInterface,
    MessageCorrelationResultWithVariablesInterface,
    ProcessInstanceInterface
};
use Jabe\Variable\VariableMapInterface;

class MessageCorrelationResultImpl implements MessageCorrelationResultWithVariablesInterface
{
    protected $execution;
    protected $resultType;
    protected $processInstance;
    protected $variables;

    public function __construct(CorrelationHandlerResult $handlerResult)
    {
        $this->execution = $handlerResult->getExecution();
        $this->resultType = $handlerResult->getResultType();
    }

    public function getExecution(): ?ExecutionInterface
    {
        return $this->execution;
    }

    public function getProcessInstance(): ProcessInstanceInterface
    {
        return $this->processInstance;
    }

    public function setProcessInstance(ProcessInstanceInterface $processInstance): void
    {
        $this->processInstance = $processInstance;
    }

    public function getResultType(): string
    {
        return $this->resultType;
    }

    public function getVariables(): VariableMapInterface
    {
        return $this->variables;
    }

    public function setVariables(VariableMapInterface $variables): void
    {
        $this->variables = $variables;
    }
}
