<?php

namespace BpmPlatform\Engine\Impl\Core\Variable\Scope;

use BpmPlatform\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

interface VariableInstanceFactoryInterface
{
    public function build(string $name, TypedValueInterface $value, bool $isTransient): CoreVariableInstanceInterface;
}
