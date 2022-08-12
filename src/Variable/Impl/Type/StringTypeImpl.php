<?php

namespace Jabe\Variable\Impl\Type;

use Jabe\Variable\Variables;
use Jabe\Variable\Value\StringValueInterface;

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
