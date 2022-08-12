<?php

namespace Jabe\Impl\Telemetry;

use Jabe\Impl\Telemetry\Dto\ApplicationServerImpl;

class PlatformTelemetryRegistry
{
    protected static $applicationServer;

    public static function getApplicationServer(): ApplicationServerImpl
    {
        return self::$applicationServer;
    }

    public static function setApplicationServer(string $applicationServerVersion): void
    {
        if (self::$applicationServer === null) {
            self::$applicationServer = new ApplicationServerImpl(null, $applicationServerVersion);
        }
    }

    public static function clear(): void
    {
        self::$applicationServer = null;
    }
}
