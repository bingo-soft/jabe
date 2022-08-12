<?php

namespace Jabe\Variable\Impl\Type;

use Jabe\Variable\Variables;
use Jabe\Variable\Value\DateValueInterface;

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
