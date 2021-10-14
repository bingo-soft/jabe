<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

class AbstractTypedValue implements TypedValueInterface
{
    protected $value;

    protected $type;

    protected $isTransient;

    public function __construct($value, ValueTypeInterface $type)
    {
        $this->value = $value;
        $this->type = $type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType(): ?ValueTypeInterface
    {
        return $this->type;
    }

    public function __toString()
    {
        return sprintf("Value '%s' of type '%s', isTransient=%s", $this->value, $this->type, $this->isTransient);
    }

    public function isTransient(): bool
    {
        return $this->isTransient;
    }

    public function setTransient(bool $isTransient): void
    {
        $this->isTransient = $isTransient;
    }
}
