<?php

namespace Jabe\Engine\Impl\Telemetry\Dto;

use Jabe\Engine\Telemetry\{
    ApplicationServerInterface,
    DatabaseInterface,
    InternalsInterface,
    LicenseKeyDataInterface
};

class InternalsImpl implements InternalsInterface
{
    public const SERIALIZED_APPLICATION_SERVER = "application-server";
    public const SERIALIZED_INTEGRATION = "integration";
    public const SERIALIZED_LICENSE_KEY = "license-key";
    public const SERIALIZED_TELEMETRY_ENABLED = "telemetry-enabled";

    protected $database;
    protected $applicationServer;
    protected $licenseKey;
    protected $commands = [];
    protected $integration = [];
    protected $metrics = [];
    protected $webapps = [];

    protected $telemetryEnabled;

    public function __construct(/*DatabaseImpl|InternalsImpl*/$databaseOrInternals, ApplicationServerInterface $server = null, LicenseKeyDataInterface $licenseKey = null)
    {
        if ($databaseOrInternals instanceof DatabaseInterface) {
            $this->database = $databaseOrInternals;
            $this->applicationServer = $server;
            $this->licenseKey = $licenseKey;
        } elseif ($databaseOrInternals instanceof InternalsInterface) {
            $this->database = $databaseOrInternals->database;
            $this->applicationServer = $databaseOrInternals->applicationServer;
            $this->licenseKey = $databaseOrInternals->licenseKey;
            $this->integration = $databaseOrInternals->getIntegration();
            $this->commands = $databaseOrInternals->getCommands();
            $this->metrics = $databaseOrInternals->getMetrics();
            $this->telemetryEnabled = $databaseOrInternals->telemetryEnabled;
            $this->webapps = $databaseOrInternals->webapps;
        }
    }

    public function __toString()
    {
        $commands = [];
        foreach ($this->commands as $command) {
            $commands[] = json_decode($command);
        }
        $integrations = [];
        foreach ($this->integration as $integration) {
            $integrations[] = json_decode($integration);
        }
        return json_encode([
            'database' => json_decode($this->database),
            'applicationServer' => json_decode($this->applicationServer),
            'licenseKey' => json_decode($this->licenseKey),
            'commands' => $commands,
            'integration' => $integrations
        ]);
    }

    public function getDatabase(): DatabaseInterface
    {
        return $this->database;
    }

    public function setDatabase(DatabaseInterface $database): void
    {
        $this->database = $database;
    }

    public function getApplicationServer(): ApplicationServerInterface
    {
        return $this->applicationServer;
    }

    public function setApplicationServer(ApplicationServerInterface $applicationServer): void
    {
        $this->applicationServer = $applicationServer;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function setCommands(array $commands): void
    {
        $this->commands = $commands;
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function setMetrics(array $metrics): void
    {
        $this->metrics = $metrics;
    }

    public function mergeDynamicData(InternalsInterface $other): void
    {
        $this->commands = $other->commands;
        $this->metrics = $other->metrics;
    }

    public function getIntegration(): array
    {
        return $this->integration;
    }

    public function setIntegration(array $integration): void
    {
        $this->integration = $integration;
    }

    public function getLicenseKey(): LicenseKeyDataInterface
    {
        return $this->licenseKey;
    }

    public function setLicenseKey(LicenseKeyDataInterface $licenseKey): void
    {
        $this->licenseKey = $licenseKey;
    }

    public function getTelemetryEnabled(): bool
    {
        return $this->telemetryEnabled;
    }

    public function setTelemetryEnabled(bool $telemetryEnabled): void
    {
        $this->telemetryEnabled = $telemetryEnabled;
    }

    public function getWebapps(): array
    {
        return $this->webapps;
    }

    public function setWebapps(array $webapps): void
    {
        $this->webapps = $webapps;
    }
}
