<?php

namespace Jabe\Engine\Variable\Value;

use Jabe\Engine\Variable\Type\PrimitiveValueTypeInterface;

interface PrimitiveValueInterface extends TypedValueInterface
{
    public function getValue();

    public function getType(): PrimitiveValueTypeInterface;
}
