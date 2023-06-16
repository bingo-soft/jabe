<?php

namespace Tests\Bpmn\Event\Compensate;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};

class SetLocalVariableListener implements ExecutionListenerInterface
{
    protected $variableName;
    protected $variableValue;

    public function __construct(string $variableName, string $variableValue)
    {
        $this->variableName = $variableName;
        $this->variableValue = $variableValue;
    }

    public function __serialize(): array
    {
        return [
            'variableName' => $this->variableName,
            'variableValue' => $this->variableValue
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->variableName = $data['variableName'];
        $this->variableValue = $data['variableValue'];
    }

    public function notify($execution): void
    {
        $execution->setVariableLocal($this->variableName, $this->variableValue);
    }
}
