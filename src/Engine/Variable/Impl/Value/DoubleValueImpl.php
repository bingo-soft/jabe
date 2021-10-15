<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\ValueTypeTrait;
use BpmPlatform\Engine\Variable\Value\DoubleValueInterface;

class DoubleValueImpl extends PrimitiveTypeValueImpl implements DoubleValueInterface
{
    public function __construct(?float $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueTypeTrait::getDouble());
        if ($isTransient != null) {
            $this->isTransient = $isTransient;
        }
    }
}
