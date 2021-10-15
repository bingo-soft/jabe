<?php

namespace BpmPlatform\Engine\Variable\Impl\Type;

use BpmPlatform\Engine\Variable\Variables;
use BpmPlatform\Engine\Variable\Value\StringValueInterface;

class NumberTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "number");
    }

    public function createValue($value, ?array $valueInfo = null): StringValueInterface
    {
        return Variables::stringValue($value, $this->isTransient($valueInfo));
    }

    public function isAbstract(): bool
    {
        return true;
    }
}
