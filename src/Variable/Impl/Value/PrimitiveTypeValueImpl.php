<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Type\PrimitiveValueTypeInterface;
use Jabe\Variable\Value\PrimitiveValueInterface;

class PrimitiveTypeValueImpl extends AbstractTypedValue implements PrimitiveValueInterface
{
    public function __construct($value, PrimitiveValueTypeInterface $type)
    {
        parent::__construct($value, $type);
    }

    public function getType(): PrimitiveValueTypeInterface
    {
        return parent::getType();
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
