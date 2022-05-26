<?php

namespace Jabe\Engine\Variable\Impl\Value;

use Jabe\Engine\Variable\Type\ValueType;
use Jabe\Engine\Variable\Value\BytesValueInterface;

class BytesValueImpl extends PrimitiveTypeValueImpl implements BytesValueInterface
{
    public function __construct(?string $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getBytes());
        if ($isTransient != null) {
            $this->isTransient = $isTransient;
        }
    }
}
