<?php

namespace Jabe\Impl;

class HistoricActivityStatisticsQueryProperty
{
    private static $ACTIVITY_ID;

    public function activityId(): QueryPropertyImpl
    {
        if (self::$ACTIVITY_ID === null) {
            self::$ACTIVITY_ID = new QueryPropertyImpl("ID_");
        }
        return self::$ACTIVITY_ID;
    }
}
