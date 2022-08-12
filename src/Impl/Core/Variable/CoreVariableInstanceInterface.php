<?php

namespace Jabe\Impl\Core\Variable;

use Jabe\Variable\Value\TypedValueInterface;

interface CoreVariableInstanceInterface
{
    public function getName(): string;

    public function getTypedValue(bool $deserializeValue): TypedValueInterface;

    public function setValue(TypedValueInterface $typedValue): void;
}
