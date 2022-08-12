<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceLifecycleListenerInterface
};

class VariableInstanceEntityPersistenceListener implements VariableInstanceLifecycleListenerInterface
{
    private static $INSTANCE;

    public static function instance(): VariableInstanceLifecycleListenerInterface
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new VariableInstanceEntityPersistenceListener();
        }
        return self::$INSTANCE;
    }

    private function __construct()
    {
    }

    public function onCreate(VariableInstanceEntity $variable, AbstractVariableScope $sourceScope): void
    {
        VariableInstanceEntity::insert($variable);
    }

    public function onDelete(VariableInstanceEntity $variable, AbstractVariableScope $sourceScope): void
    {
        $variable->delete();
    }

    public function onUpdate(VariableInstanceEntity $variable, AbstractVariableScope $sourceScope): void
    {
    }
}
