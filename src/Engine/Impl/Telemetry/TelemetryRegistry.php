<?php

namespace BpmPlatform\Engine\Impl\Telemetry;

use BpmPlatform\Engine\Impl\Telemetry\Dto\{
    ApplicationServer,
    LicenseKeyData
};

class TelemetryRegistry
{
    protected $commands = [];
    protected $applicationServer;
    protected $licenseKey;
    protected $integration;
    protected $webapps = [];
    protected $isCollectingTelemetryDataEnabled = false;

    public function getApplicationServer(): ApplicationServer
    {
        if ($this->applicationServer == null) {
            $this->applicationServer = PlatformTelemetryRegistry::getApplicationServer();
        }
        return $this->applicationServer;
    }

    public function setApplicationServer(ApplicationServer $applicationServer): void
    {
        $this->applicationServer = $applicationServer;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getIntegration(): string
    {
        return $this->integration;
    }

    public function setIntegration(string $integration): void
    {
        $this->camundaIntegration = $integration;
    }

    public function getLicenseKey(): LicenseKeyData
    {
        return $this->licenseKey;
    }

    public function setLicenseKey(LicenseKeyData $licenseKey): void
    {
        $this->licenseKey = $licenseKey;
    }

    public function getWebapps(): array
    {
        return $this->webapps;
    }

    public function setWebapps(array $webapps): void
    {
        $this->webapps = $webapps;
    }

    public function isCollectingTelemetryDataEnabled(): bool
    {
        return $this->isCollectingTelemetryDataEnabled;
    }

    public function setCollectingTelemetryDataEnabled(bool $isTelemetryEnabled): void
    {
        $this->isCollectingTelemetryDataEnabled = $isTelemetryEnabled;
    }

    public function markOccurrence(string $name, int $times = 1): void
    {
        $counter = $commands[$name];
        if ($counter == null) {
            $counter = new CommandCounter($name);
            $this->commands[$name] = $counter;
        }

        $counter->mark($times);
    }

    public function addWebapp(string $webapp): void
    {
        if (!in_array($webapp, $this->webapps)) {
            $this->webapps[] = $webapp;
        }
    }

    public function clear(): void
    {
        $this->commands = [];
        $this->licenseKey = null;
        $this->applicationServer = null;
        $this->webapps = [];
    }
}
