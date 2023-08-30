<?php

namespace Jabe\Variable\Type;

interface PrimitiveValueTypeInterface extends ValueTypeInterface
{
    public function getPhpType(): ?string;
}
