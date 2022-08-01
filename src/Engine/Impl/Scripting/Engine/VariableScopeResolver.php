<?php

namespace Jabe\Engine\Impl\Scripting\Engine;

use Jabe\Engine\Delegate\VariableScopeInterface;

class VariableScopeResolver implements ResolverInterface
{
    protected $variableScope;
    protected $variableScopeKey;

    public function __construct(VariableScopeInterface $variableScope)
    {
        $this->variableScopeKey = $variableScope->getVariableScopeKey();
        $this->variableScope = $variableScope;
    }

    public function containsKey($key): bool
    {
        return $this->variableScopeKey == $key || $this->variableScope->hasVariable($key);
    }

    public function get($key)
    {
        if ($this->variableScopeKey == $key) {
            return $this->variableScope;
        }
        return $this->variableScope->getVariable($key);
    }

    public function keySet(): array
    {
        // get variable names will return a new set instance
        $variableNames = $this->variableScope->getVariableNames();
        $variableNames[] = $this->variableScopeKey;
        return $variableNames;
    }
}
