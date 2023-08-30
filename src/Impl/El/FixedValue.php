<?php

namespace Jabe\Impl\El;

use Jabe\ProcessEngineException;
use Jabe\Delegate\{
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

    public function getValue(VariableScopeInterface $variableScope, ?BaseDelegateExecutionInterface $contextExecution = null)
    {
        return $this->value;
    }

    public function setValue($value, ?VariableScopeInterface $variableScope = null, ?BaseDelegateExecutionInterface $contextExecution = null): void
    {
        throw new ProcessEngineException("Cannot change fixed value");
    }

    public function getExpressionText(): ?string
    {
        return strval($this->value);
    }

    public function isLiteralText(): bool
    {
        return true;
    }
}
