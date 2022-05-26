<?php

namespace Jabe\Engine\Variable\Impl\Type;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Value\BytesValueInterface;

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
