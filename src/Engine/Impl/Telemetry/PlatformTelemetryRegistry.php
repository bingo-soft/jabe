<?php

namespace Jabe\Engine\Impl\Telemetry;

use Jabe\Engine\Impl\Telemetry\Dto\ApplicationServerImpl;

class PlatformTelemetryRegistry
{
    protected static $applicationServer;

    public static function getApplicationServer(): ApplicationServerImpl
    {
        return $this->applicationServer;
    }

    public static function setApplicationServer(string $applicationServerVersion): void
    {
        if ($this->applicationServer === null) {
            $this->applicationServer = new ApplicationServerImpl(null, $applicationServerVersion);
        }
    }

    public static function clear(): void
    {
        $this->applicationServer = null;
    }
}
