<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Value\DateValueInterface;

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
