<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Core\Variable\Scope\VariableInstanceFactoryInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

class VariableInstanceEntityFactory implements VariableInstanceFactoryInterface
{
    private static $INSTANCE;

    public static function instance(): VariableInstanceFactoryInterface
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new VariableInstanceEntityFactory();
        }
        return self::$INSTANCE;
    }

    private function __construct()
    {
    }

    public function build(string $name, TypedValueInterface $value, bool $isTransient): VariableInstanceEntity
    {
        return VariableInstanceEntity::create($name, $value, $isTransient);
    }
}
