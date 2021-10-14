<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\ValueTypeTrait;
use BpmPlatform\Engine\Variable\Value\IntegerValueInterface;

class IntegerValueImpl extends PrimitiveTypeValueImpl implements IntegerValueInterface
{
    use ValueTypeTrait;

    public function __construct(int $value, ?bool $isTransient = null)
    {
        parent::__construct($value, $this->getInteger());
        if ($isTransient != null) {
            $this->isTransient = $isTransient;
        }
    }
}
