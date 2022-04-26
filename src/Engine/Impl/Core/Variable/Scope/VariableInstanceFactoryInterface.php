<?php

namespace Jabe\Engine\Impl\Core\Variable\Scope;

use Jabe\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

interface VariableInstanceFactoryInterface
{
    public function build(string $name, TypedValueInterface $value, bool $isTransient): CoreVariableInstanceInterface;
}
