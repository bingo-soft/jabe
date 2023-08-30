<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceLifecycleListenerInterface
};
use Jabe\Impl\Core\Variable\CoreVariableInstanceInterface;

class VariableInstanceSequenceCounterListener implements VariableInstanceLifecycleListenerInterface
{
    private static $INSTANCE;

    public static function instance(): VariableInstanceLifecycleListenerInterface
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new VariableInstanceSequenceCounterListener();
        }
        return self::$INSTANCE;
    }

    private function __construct()
    {
    }

    public function onCreate(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
    }

    public function onDelete(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $variableInstance->incrementSequenceCounter();
    }

    public function onUpdate(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $variableInstance->incrementSequenceCounter();
    }
}
