<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\BooleanValueInterface;

class BooleanValueImpl extends PrimitiveTypeValueImpl implements BooleanValueInterface
{
    public function __construct(?bool $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getBoolean());
        if ($isTransient !== null) {
            $this->isTransient = $isTransient;
        }
    }
}
