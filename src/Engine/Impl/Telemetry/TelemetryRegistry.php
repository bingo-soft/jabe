<?php

namespace Jabe\Engine\Impl\Telemetry;

use Jabe\Engine\Impl\Telemetry\Dto\{
    ApplicationServerImpl,
    LicenseKeyDataImpl
};

class TelemetryRegistry
{
    protected $commands = [];
    protected $applicationServer;
    protected $licenseKey;
    protected $integration;
    protected $webapps = [];
    protected $isCollectingTelemetryDataEnabled = false;

    public function getApplicationServer(): ApplicationServerImpl
    {
        if ($this->applicationServer == null) {
            $this->applicationServer = PlatformTelemetryRegistry::getApplicationServer();
        }
        return $this->applicationServer;
    }

    public function setApplicationServer(ApplicationServerImpl $applicationServer): void
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
        $this->integration = $integration;
    }

    public function getLicenseKey(): ?LicenseKeyDataImpl
    {
        return $this->licenseKey;
    }

    public function setLicenseKey(LicenseKeyDataImpl $licenseKey): void
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
        if (!array_key_exists($name, $this->commands)) {
            $counter = new CommandCounter($name);
            $this->commands[$name] = $counter;
        } else {
            $counter = $this->commands[$name];
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
