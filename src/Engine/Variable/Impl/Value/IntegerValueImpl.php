<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\ValueType;
use BpmPlatform\Engine\Variable\Value\IntegerValueInterface;

class IntegerValueImpl extends PrimitiveTypeValueImpl implements IntegerValueInterface
{
    public function __construct(?int $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getInteger());
        if ($isTransient != null) {
            $this->isTransient = $isTransient;
        }
    }
}
