<?php

namespace Jabe\Impl;

use Jabe\Query\QueryPropertyInterface;

class CleanableHistoricInstanceReportProperty
{
    private static $FINISHED_AMOUNT;

    public function finishedAmount(): QueryPropertyInterface
    {
        if (self::$FINISHED_AMOUNT === null) {
            self::$FINISHED_AMOUNT = new QueryPropertyImpl("FINISHED_");
        }
        return self::$FINISHED_AMOUNT;
    }
}
