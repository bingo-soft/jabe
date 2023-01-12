<?php

namespace Jabe\Variable\Impl\Context;

use Jabe\Variable\Context\VariableContextInterface;
use Jabe\Variable\Value\TypedValueInterface;

class SingleVariableContext implements VariableContextInterface
{
    protected $typedValue;
    protected $name;

    public function __construct(?string $name, TypedValueInterface $typedValue)
    {
        $this->name = $name;
        $this->typedValue = $typedValue;
    }

    public function resolve(?string $variableName): ?TypedValueInterface
    {
        if ($this->containsVariable($variableName)) {
            return $this->typedValue;
        } else {
            return null;
        }
    }

    public function containsVariable(?string $name = null): bool
    {
        if ($this->name === null) {
            return $name === null;
        } else {
            return $this->name == $name;
        }
    }

    public function keySet(): array
    {
        return [$this->name];
    }

    public static function singleVariable(?string $name, TypedValueInterface $value): SingleVariableContext
    {
        return new SingleVariableContext($name, $value);
    }
}
