<?php

namespace Jabe\Delegate;

interface ExpressionInterface
{
    public function getValue(VariableScopeInterface $variableScope);

    public function setValue($value, VariableScopeInterface $variableScope): void;

    public function getExpressionText(): ?string;

    public function isLiteralText(): bool;
}
