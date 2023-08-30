<?php

namespace Jabe\Application\Impl;

class ProcessApplicationContextImpl
{
    protected static $currentProcessApplication;

    public static function get(): ?ProcessApplicationIdentifier
    {
        return self::$currentProcessApplication;
    }

    public static function set(ProcessApplicationIdentifier $identifier): void
    {
        self::$currentProcessApplication = $identifier;
    }

    public static function clear(): void
    {
        self::$currentProcessApplication = null;
    }
}
