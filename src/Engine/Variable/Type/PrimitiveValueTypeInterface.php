<?php

namespace Jabe\Engine\Variable\Type;

interface PrimitiveValueTypeInterface extends ValueTypeInterface
{
    public function getPhpType(): string;
}
