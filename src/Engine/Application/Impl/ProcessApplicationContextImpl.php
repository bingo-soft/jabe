<?php

namespace BpmPlatform\Engine\Application\Impl;

class ProcessApplicationContextImpl
{
    protected static $currentProcessApplication;

    public static function get(): ?ProcessApplicationIdentifier
    {
        return $this->currentProcessApplication;
    }

    public static function set(ProcessApplicationIdentifier $identifier): void
    {
        $this->currentProcessApplication = $identifier;
    }

    public static function clear(): void
    {
        $this->currentProcessApplication = null;
    }
}
