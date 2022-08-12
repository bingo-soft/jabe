<?php

namespace Jabe\Impl\Event;

class EventType
{
    public static $MESSAGE;

    public static function message(): EventType
    {
        if (self::$MESSAGE === null) {
            self::$MESSAGE = new EventType("message");
        }
        return self::$MESSAGE;
    }

    public static $SIGNAL;

    public static function signal(): EventType
    {
        if (self::$SIGNAL === null) {
            self::$SIGNAL = new EventType("signal");
        }
        return self::$SIGNAL;
    }

    public static $COMPENSATE;

    public static function compensate(): EventType
    {
        if (self::$COMPENSATE === null) {
            self::$COMPENSATE = new EventType("compensate");
        }
        return self::$COMPENSATE;
    }

    public static $CONDITONAL;

    public static function conditional(): EventType
    {
        if (self::$CONDITONAL === null) {
            self::$CONDITONAL = new EventType("conditional");
        }
        return self::$CONDITONAL;
    }

    private $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
