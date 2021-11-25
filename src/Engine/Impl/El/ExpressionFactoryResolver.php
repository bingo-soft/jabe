<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Impl\Util\El\ExpressionFactory;
use BpmPlatform\Engine\Impl\Juel\ExpressionFactoryImpl;

abstract class ExpressionFactoryResolver
{
    public static function resolveExpressionFactory(): ExpressionFactory
    {
        // Return instance of custom JUEL implementation
        return new ExpressionFactoryImpl();
    }
}
