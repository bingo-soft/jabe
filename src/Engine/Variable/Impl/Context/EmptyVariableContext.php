<?php

namespace BpmPlatform\Engine\Variable\Impl\Context;

use BpmPlatform\Engine\Variable\Context\VariableContextInterface;
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

class EmptyVariableContext implements VariableContextInterface
{
    private static $INSTANCE;

    public static function getInstance(): EmptyVariableContext
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new EmptyVariableContext();
        }
        return self::$INSTANCE;
    }

    private function __construct()
    {
    }

    public function resolve(string $variableName): ?TypedValueInterface
    {
        return null;
    }

    public function containsVariable(string $variableName): bool
    {
        return false;
    }

    public function keySet(): array
    {
        return [];
    }
}
