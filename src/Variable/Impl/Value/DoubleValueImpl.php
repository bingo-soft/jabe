<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\DoubleValueInterface;

class DoubleValueImpl extends PrimitiveTypeValueImpl implements DoubleValueInterface
{
    public function __construct(?float $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getDouble());
        if ($isTransient !== null) {
            $this->isTransient = $isTransient;
        }
    }
}
