<?php

namespace Jabe\Variable\Impl\Type;

use Jabe\Variable\Variables;

class BytesTypeImpl extends AbstractValueTypeImpl
{
    public function __construct()
    {
        parent::__construct(null, "bytes");
    }

    public function createValue($value, ?array $valueInfo = null): BooleanValueInterface
    {
        return Variables::byteArrayValue($value, $this->isTransient($valueInfo));
    }
}
