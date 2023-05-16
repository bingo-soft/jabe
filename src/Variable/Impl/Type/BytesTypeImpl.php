<?php

namespace Jabe\Variable\Impl\Type;

use Jabe\Variable\Variables;
use Jabe\Variable\Value\BytesValueInterface;

class BytesTypeImpl extends PrimitiveValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "bytes");
    }

    public function createValue($value, ?array $valueInfo = null): BytesValueInterface
    {
        return Variables::byteArrayValue($value, $this->isTransient($valueInfo));
    }
}
