<?php

namespace BpmPlatform\Engine\Variable\Impl\Value;

use BpmPlatform\Engine\Variable\Type\ValueTypeTrait;
use BpmPlatform\Engine\Variable\Value\BooleanValueInterface;

class BooleanValueImpl extends PrimitiveTypeValueImpl implements BooleanValueInterface
{
    use ValueTypeTrait;

    public function __construct(bool $value, ?bool $isTransient = null)
    {
        parent::__construct($value, $this->getBoolean());
        if ($isTransient != null) {
            $this->isTransient = $isTransient;
        }
    }
}
