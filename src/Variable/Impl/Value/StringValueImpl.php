<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\StringValueInterface;

class StringValueImpl extends PrimitiveTypeValueImpl implements StringValueInterface
{
    public function __construct(?string $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getString());
        if ($isTransient !== null) {
            $this->isTransient = $isTransient;
        }
    }
}
