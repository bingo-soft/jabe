<?php

namespace BpmPlatform\Engine\Variable\Impl\Type;

use BpmPlatform\Engine\Variable\Variables;
use BpmPlatform\Engine\Variable\Value\StringValueInterface;

class StringTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "string");
    }

    public function createValue($value, ?array $valueInfo = null): StringValueInterface
    {
        return Variables::stringValue($value, $this->isTransient($valueInfo));
    }
}
