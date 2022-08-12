<?php

namespace Jabe\Variable\Impl\Type;

use Jabe\Variable\Variables;
use Jabe\Variable\Value\TypedValueInterface;

class NullTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "NULL");
    }

    public function createValue($value, ?array $valueInfo = null): TypedValueInterface
    {
        return Variables::untypedNullValue($this->isTransient($valueInfo));
    }
}
