<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\ValueTypeTrait;
use BpmPlatform\Engine\Variable\Value\StringValueInterface;

class StringValueImpl extends PrimitiveTypeValueImpl implements StringValueInterface
{
    public function __construct(?string $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueTypeTrait::getString());
        if ($isTransient != null) {
            $this->isTransient = $isTransient;
        }
    }
}
