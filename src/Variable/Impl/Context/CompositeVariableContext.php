<?php

namespace Jabe\Variable\Impl\Context;

use Jabe\Variable\Context\VariableContextInterface;
use Jabe\Variable\Value\TypedValueInterface;

class CompositeVariableContext implements VariableContextInterface
{
    protected $delegateContexts;

    public function __construct(array $delegateContexts)
    {
        $this->delegateContexts = $delegateContexts;
    }

    public function resolve(?string $variableName): ?TypedValueInterface
    {
        foreach ($this->delegateContexts as $variableContext) {
            $resolvedValue = $variableContext->resolve($variableName);
            if ($resolvedValue !== null) {
                return $resolvedValue;
            }
        }

        return null;
    }

    public function containsVariable(?string $name): bool
    {
        foreach ($this->delegateContexts as $variableContext) {
            if ($variableContext->containsVariable($name)) {
                return true;
            }
        }

        return false;
    }

    public function keySet(): array
    {
        $keySet = [];
        foreach ($this->delegateContexts as $variableContext) {
            $keySet = array_merge($keySet, $variableContext->keySet());
        }
        return $keySet;
    }

    public static function compose(array $variableContexts): CompositeVariableContext
    {
        return new CompositeVariableContext($variableContexts);
    }
}
