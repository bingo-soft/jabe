<?php

namespace Jabe\Engine\Impl\Telemetry;

use Jabe\Engine\Impl\Telemetry\Dto\ApplicationServer;

class PlatformTelemetryRegistry
{
    protected static $applicationServer;

    public static function getApplicationServer(): ApplicationServer
    {
        return $this->applicationServer;
    }

    public static function setApplicationServer(string $applicationServerVersion): void
    {
        if ($this->applicationServer == null) {
            $this->applicationServer = new ApplicationServer(null, $applicationServerVersion);
        }
    }

    public static function clear(): void
    {
        $this->applicationServer = null;
    }
}
