<?php

namespace BpmPlatform\Engine\Impl\Core\Variable;

use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

interface CoreVariableInstanceInterface
{
    public function getName(): string;

    public function getTypedValue(bool $deserializeValue): TypedValueInterface;

    public function setValue(TypedValueInterface $typedValue): void;
}
