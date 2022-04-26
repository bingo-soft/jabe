<?php

namespace Jabe\Engine\Impl\Core\Variable\Scope;

use Jabe\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;

interface VariableInstanceLifecycleListenerInterface
{
    public function onCreate(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void;

    public function onDelete(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void;

    public function onUpdate(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void;
}
