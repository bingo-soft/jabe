<?php

namespace Jabe\Engine\Impl\Pvm\Runtime;

class ActivityInstanceState
{
    private static $DEFAULT;
    private static $SCOPE_COMPLETE;
    private static $CANCELED;
    private static $STARTING;
    private static $ENDING;

    public static function default(): ActivityInstanceStateImpl
    {
        if (self::$DEFAULT === null) {
            self::$DEFAULT = new ActivityInstanceStateImpl(0, "default");
        }
        return self::$DEFAULT;
    }

    public static function scopeComplete(): ActivityInstanceStateImpl
    {
        if (self::$SCOPE_COMPLETE === null) {
            self::$SCOPE_COMPLETE = new ActivityInstanceStateImpl(1, "scopeComplete");
        }
        return self::$SCOPE_COMPLETE;
    }

    public static function canceled(): ActivityInstanceStateImpl
    {
        if (self::$CANCELED === null) {
            self::$CANCELED = new ActivityInstanceStateImpl(2, "canceled");
        }
        return self::$CANCELED;
    }

    public static function starting(): ActivityInstanceStateImpl
    {
        if (self::$STARTING === null) {
            self::$STARTING = new ActivityInstanceStateImpl(3, "starting");
        }
        return self::$STARTING;
    }

    public static function ending(): ActivityInstanceStateImpl
    {
        if (self::$ENDING === null) {
            self::$ENDING = new ActivityInstanceStateImpl(4, "ending");
        }
        return self::$ENDING;
    }
}
