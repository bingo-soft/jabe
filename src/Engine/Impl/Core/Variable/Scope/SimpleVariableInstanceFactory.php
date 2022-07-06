<?php

namespace Jabe\Engine\Impl\Core\Variable\Scope;

use Jabe\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

class SimpleVariableInstanceFactory implements VariableInstanceFactoryInterface
{
    private static $INSTANCE;

    private function __construct()
    {
    }

    public static function getInstance(): SimpleVariableInstanceFactory
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new SimpleVariableInstanceFactory();
        }
        return self::$INSTANCE;
    }

    public function build(string $name, TypedValueInterface $value, bool $isTransient): CoreVariableInstanceInterface
    {
        return new SimpleVariableInstance($name, $value);
    }
}
