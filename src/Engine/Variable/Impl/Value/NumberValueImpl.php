<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\ValueTypeTrait;
use BpmPlatform\Engine\Variable\Value\NumberValueInterface;

class NumberValueImpl extends PrimitiveTypeValueImpl implements NumberValueInterface
{
    public function __construct($value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueTypeTrait::getNumber());
        if ($isTransient != null) {
            $this->isTransient = $isTransient;
        }
    }
}
