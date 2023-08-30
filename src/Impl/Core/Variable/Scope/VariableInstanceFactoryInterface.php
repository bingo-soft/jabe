<?php

namespace Jabe\Impl\Core\Variable\Scope;

use Jabe\Impl\Core\Variable\CoreVariableInstanceInterface;
use Jabe\Variable\Value\TypedValueInterface;

interface VariableInstanceFactoryInterface
{
    public function build(?string $name, TypedValueInterface $value, bool $isTransient): CoreVariableInstanceInterface;
}
