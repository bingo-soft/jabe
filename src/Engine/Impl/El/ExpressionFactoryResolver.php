<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\Impl\Util\El\ExpressionFactory;
use Jabe\Engine\Impl\Juel\ExpressionFactoryImpl;

abstract class ExpressionFactoryResolver
{
    public static function resolveExpressionFactory(): ExpressionFactory
    {
        // Return instance of custom JUEL implementation
        return new ExpressionFactoryImpl();
    }
}
