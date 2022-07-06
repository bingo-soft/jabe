<?php

namespace Jabe\Engine\Impl;

class TaskQueryVariableValueComparable
{
    protected $variableValue;

    public function __construct(TaskQueryVariableValue $variableValue)
    {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): TaskQueryVariableValue
    {
        return $this->variableValue;
    }

    public function equals($o = null): bool
    {
        if ($this == $o) {
            return true;
        }
        if ($o === null || get_class($this) != get_class($o)) {
            return false;
        }

        $other = $o->getVariableValue();

        return $this->variableValue->getName() == $other->getName()
                && $this->variableValue->isProcessInstanceVariable() == $other->isProcessInstanceVariable()
                && $this->variableValue->isLocal() == $other->isLocal();
    }
}
