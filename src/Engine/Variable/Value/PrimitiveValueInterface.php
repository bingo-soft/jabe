<?php

namespace BpmPlatform\Engine\Variable\Value;

use BpmPlatform\Engine\Variable\Type\PrimitiveValueTypeInterface;

interface PrimitiveValueInterface extends TypedValueInterface
{
    public function getValue();

    public function getType(): PrimitiveValueTypeInterface;
}
