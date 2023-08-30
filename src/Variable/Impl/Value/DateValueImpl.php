<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\DateValueInterface;

class DateValueImpl extends PrimitiveTypeValueImpl implements DateValueInterface
{
    public function __construct(?string $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getDate());
        if ($isTransient !== null) {
            $this->isTransient = $isTransient;
        }
    }
}
