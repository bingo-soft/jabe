<?php

namespace Jabe\Variable\Impl\Value;

use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\BytesValueInterface;

class BytesValueImpl extends PrimitiveTypeValueImpl implements BytesValueInterface
{
    public function __construct(?string $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getBytes());
        if ($isTransient !== null) {
            $this->isTransient = $isTransient;
        }
    }
}
