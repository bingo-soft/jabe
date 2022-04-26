<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Value\StringValueInterface;

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
