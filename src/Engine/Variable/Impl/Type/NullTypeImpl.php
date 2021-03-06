<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Value\TypedValueInterface;

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
