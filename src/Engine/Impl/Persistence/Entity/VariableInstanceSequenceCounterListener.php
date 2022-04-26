<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceLifecycleListenerInterface
};

class VariableInstanceSequenceCounterListener implements VariableInstanceLifecycleListenerInterface
{
    private static $INSTANCE;

    public static function instance(): VariableInstanceLifecycleListenerInterface
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new VariableInstanceSequenceCounterListener();
        }
        return self::$INSTANCE;
    }

    private function __construct()
    {
    }

    public function onCreate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
    }

    public function onDelete(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $variableInstance->incrementSequenceCounter();
    }

    public function onUpdate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $variableInstance->incrementSequenceCounter();
    }
}
