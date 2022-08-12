<?php

namespace Jabe\Impl\History;

abstract class HistoryLevel extends AbstractHistoryLevel
{
    private static $HISTORY_LEVEL_NONE;
    private static $HISTORY_LEVEL_ACTIVITY;
    private static $HISTORY_LEVEL_AUDIT;
    private static $HISTORY_LEVEL_FULL;

    public static function historyLevelNone(): AbstractHistoryLevel
    {
        if (self::$HISTORY_LEVEL_NONE === null) {
            self::$HISTORY_LEVEL_NONE = new HistoryLevelNone();
        }
        return self::$HISTORY_LEVEL_NONE;
    }

    public static function historyLevelActivity(): AbstractHistoryLevel
    {
        if (self::$HISTORY_LEVEL_ACTIVITY === null) {
            self::$HISTORY_LEVEL_ACTIVITY = new HistoryLevelActivity();
        }
        return self::$HISTORY_LEVEL_ACTIVITY;
    }

    public static function historyLevelAudit(): AbstractHistoryLevel
    {
        if (self::$HISTORY_LEVEL_AUDIT === null) {
            self::$HISTORY_LEVEL_AUDIT = new HistoryLevelAudit();
        }
        return self::$HISTORY_LEVEL_AUDIT;
    }

    public static function historyLevelFull(): AbstractHistoryLevel
    {
        if (self::$HISTORY_LEVEL_FULL === null) {
            self::$HISTORY_LEVEL_FULL = new HistoryLevelFull();
        }
        return self::$HISTORY_LEVEL_FULL;
    }
}
