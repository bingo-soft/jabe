<?php

namespace Jabe\Engine\Variable\Impl\Value;

use Jabe\Engine\Variable\Type\ValueType;
use Jabe\Engine\Variable\Value\IntegerValueInterface;

class IntegerValueImpl extends PrimitiveTypeValueImpl implements IntegerValueInterface
{
    public function __construct(?int $value, ?bool $isTransient = null)
    {
        parent::__construct($value, ValueType::getInteger());
        if ($isTransient !== null) {
            $this->isTransient = $isTransient;
        }
    }
}
