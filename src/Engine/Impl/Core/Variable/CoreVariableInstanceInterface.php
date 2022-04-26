<?php

namespace Jabe\Engine\Impl\Core\Variable;

use Jabe\Engine\Variable\Value\TypedValueInterface;

interface CoreVariableInstanceInterface
{
    public function getName(): string;

    public function getTypedValue(bool $deserializeValue): TypedValueInterface;

    public function setValue(TypedValueInterface $typedValue): void;
}
