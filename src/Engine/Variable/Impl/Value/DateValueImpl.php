<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\ValueType;
use BpmPlatform\Engine\Variable\Value\DateValueInterface;

class DateValueImpl extends PrimitiveTypeValueImpl implements DateValueInterface
{
    public function __construct(?string $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getDate());
        if ($isTransient != null) {
            $this->isTransient = $isTransient;
        }
    }
}
