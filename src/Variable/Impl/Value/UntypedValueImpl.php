<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Value\TypedValueInterface;
use Jabe\Variable\Type\ValueTypeInterface;

class UntypedValueImpl implements TypedValueInterface
{
    protected $value;
    protected bool $isTransient = false;

    public function __construct($object, ?bool $isTransient = null)
    {
        $this->value = $object;
        $this->isTransient = $isTransient ?? false;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType(): ?ValueTypeInterface
    {
        return null;
    }

    public function __toString()
    {
        return sprintf("Untyped value '%s', isTransient = %s", $this->value, $this->isTransient);
    }

    public function serialize()
    {
        return json_encode([
            'value' => $this->value,
            'isTransient' => $this->isTransient
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->value = $json->value;
        $this->isTransient = $json->isTransient;
    }

    public function isTransient(): bool
    {
        return $this->isTransient;
    }

    public function setTransient(bool $isTransient): void
    {
        $this->isTransient = $isTransient;
    }

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj === null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->type === null) {
            if ($obj->type !== null) {
                return false;
            }
        } elseif (!$this->type->equals($obj->type)) {
            return false;
        }
        if ($this->value === null) {
            if ($obj->value !== null) {
                return false;
            }
        } elseif ($this->value != $obj->value) {
            return false;
        }
        if ($this->isTransient != $obj->isTransient()) {
            return false;
        }
        return true;
    }
}
