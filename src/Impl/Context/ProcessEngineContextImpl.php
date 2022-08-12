<?php

namespace Jabe\Impl\Context;

class ProcessEngineContextImpl
{
    protected static $commandContextNew = false;

    public static function get(): bool
    {
        return self::$commandContextNew;
    }

    public static function set(bool $requiresNew): void
    {
        self::$commandContextNew = $requiresNew;
    }

    public static function consume(): bool
    {
        $isNewCommandContext = self::$commandContextNew;
        self::clear();

        return $isNewCommandContext;
    }

    public static function clear(): void
    {
        self::$commandContextNew = false;
    }
}
