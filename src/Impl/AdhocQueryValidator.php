<?php

namespace Jabe\Impl;

use Jabe\BadUserRequestException;
use Jabe\Impl\Context\Context;

class AdhocQueryValidator implements ValidatorInterface
{

    private static $INSTANCE;

    public static function instance(): ValidatorInterface
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new AdhocQueryValidator();
        }
        return self::$INSTANCE;
    }

    public function validate($query)
    {
        if (
            !Context::getProcessEngineConfiguration()->isEnableExpressionsInAdhocQueries() &&
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
