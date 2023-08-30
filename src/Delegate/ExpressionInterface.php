<?php

namespace Jabe\Delegate;

interface ExpressionInterface
{
    public function getValue(VariableScopeInterface $variableScope, ?BaseDelegateExecutionInterface $contextExecution = null);

    public function setValue($value, ?VariableScopeInterface $variableScope = null, ?BaseDelegateExecutionInterface $contextExecution = null): void;

    public function getExpressionText(): ?string;

    public function isLiteralText(): bool;
}
