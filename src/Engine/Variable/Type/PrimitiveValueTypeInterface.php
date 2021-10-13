<?php

namespace BpmPlatform\Engine\Variable\Type;

interface PrimitiveValueTypeInterface extends ValueTypeInterface
{
    public function getPHPType(): string;
}
