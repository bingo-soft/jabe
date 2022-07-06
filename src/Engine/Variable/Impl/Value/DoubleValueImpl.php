<?php

namespace Jabe\Engine\Variable\Impl\Value;

use Jabe\Engine\Variable\Type\ValueType;
use Jabe\Engine\Variable\Value\DoubleValueInterface;

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
