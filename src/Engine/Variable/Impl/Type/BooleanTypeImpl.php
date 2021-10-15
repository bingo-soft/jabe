<?php

namespace BpmPlatform\Engine\Variable\Impl\Type;

use BpmPlatform\Engine\Variable\Variables;
use BpmPlatform\Engine\Variable\Value\BooleanValueInterface;

class BooleanTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "bool");
    }

    public function createValue($value, ?array $valueInfo = null): BooleanValueInterface
    {
        return Variables::booleanValue($value, $this->isTransient($valueInfo));
    }
}
