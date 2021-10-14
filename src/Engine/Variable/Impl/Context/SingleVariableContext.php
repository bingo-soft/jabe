<?php

namespace BpmPlatform\Engine\Variable\Impl\Context;

use BpmPlatform\Engine\Variable\Context\VariableContextInterface;
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

class SingleVariableContext implements VariableContextInterface
{
    protected $typedValue;
    protected $name;

    public function __construct(string $name, TypedValueInterface $typedValue)
    {
        $this->name = $name;
        $this->typedValue = $typedValue;
    }

    public function resolve(string $variableName): ?TypedValueInterface
    {
        if ($this->containsVariable($variableName)) {
            return $this->typedValue;
        } else {
            return null;
        }
    }

    public function containsVariable(?string $name = null): bool
    {
        if ($this->name == null) {
            return $name == null;
        } else {
            return $this->name == $name;
        }
    }

    public function keySet(): array
    {
        return [$this->name];
    }

    public static function singleVariable(string $name, TypedValueInterface $value): SingleVariableContext
    {
        return new SingleVariableContext($name, $value);
    }
}
