<?php

namespace BpmPlatform\Engine\Variable\Impl\Type;

use BpmPlatform\Engine\Variable\Variables;
use BpmPlatform\Engine\Variable\Value\DateValueInterface;

class DateTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "string");
    }

    public function createValue($value, ?array $valueInfo = null): DateValueInterface
    {
        return Variables::dateValue($value, $this->isTransient($valueInfo));
    }
}
