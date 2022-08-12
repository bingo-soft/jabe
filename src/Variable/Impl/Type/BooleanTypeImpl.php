<?php

namespace Jabe\Variable\Impl\Type;

use Jabe\Variable\Variables;
use Jabe\Variable\Value\BooleanValueInterface;

class BooleanTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "boolean");
    }

    public function createValue($value, ?array $valueInfo = null): BooleanValueInterface
    {
        return Variables::booleanValue($value, $this->isTransient($valueInfo));
    }
}
