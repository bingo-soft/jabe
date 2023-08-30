<?php

namespace Jabe\Variable\Value;

use Jabe\Variable\Type\PrimitiveValueTypeInterface;

interface PrimitiveValueInterface extends TypedValueInterface
{
    public function getValue();

    public function getType(): PrimitiveValueTypeInterface;
}
