<?php

namespace Tests\Bpmn\Event\Compensate;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};

class SetLocalVariableListener implements ExecutionListenerInterface, \Serializable
{
    protected $variableName;
    protected $variableValue;

    public function __construct(string $variableName, string $variableValue)
    {
        $this->variableName = $variableName;
        $this->variableValue = $variableValue;
    }

    public function serialize()
    {
        return json_encode([
            'variableName' => $this->variableName,
            'variableValue' => $this->variableValue
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->variableName = $json->variableName;
        $this->variableValue = $json->variableValue;
    }

    public function notify($execution): void
    {
        $execution->setVariableLocal($this->variableName, $this->variableValue);
    }
}
