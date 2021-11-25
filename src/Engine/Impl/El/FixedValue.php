<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Delegate\{
    BaseDelegateExecutionInterface,
    ExpressionInterface,
    VariableScopeInterface
};

class FixedValue implements ExpressionInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue(VariableScopeInterface $variableScope, ?BaseDelegateExecutionInterface $contextExecution)
    {
        return $this->value;
    }

    public function setValue($value, VariableScopeInterface $variableScope)
    {
        throw new ProcessEngineException("Cannot change fixed value");
    }

    public function getExpressionText(): string
    {
        return strval($this->value);
    }

    public function isLiteralText(): bool
    {
        return true;
    }
}
