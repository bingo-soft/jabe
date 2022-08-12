<?php

namespace Jabe\Impl;

use Jabe\BadUserRequestException;
use Jabe\Impl\Context\Context;

class StoredQueryValidator implements ValidatorInterface
{

    private static $INSTANCE;

    public static function instance(): ValidatorInterface
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new StoredQueryValidator();
        }
        return self::$INSTANCE;
    }

    public function validate($query)
    {
        if (
            !Context::getProcessEngineConfiguration()->isEnableExpressionsInStoredQueries() &&
            !empty($query->getExpressions())
        ) {
                throw new BadUserRequestException(
                    "Expressions are forbidden in adhoc queries. This behavior can be toggled"
                    . " in the process engine configuration"
                );
        }
    }

    public static function get(): ValidatorInterface
    {
        return self::instance();
    }
}
