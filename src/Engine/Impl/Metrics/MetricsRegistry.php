<?php

namespace Jabe\Engine\Impl\Metrics;

class MetricsRegistry
{
    protected $dbMeters = [];
    protected $telemetryMeters = [];

    public function getDbMeterByName(string $name): ?Meter
    {
        if (array_key_exists($key, $this->dbMeters)) {
            return $this->dbMeters[$name];
        }
        return null;
    }

    public function getDbMeters(): array
    {
        return $this->dbMeters;
    }

    public function getTelemetryMeters(): array
    {
        return $this->telemetryMeters;
    }

    public function clearTelemetryMetrics(): void
    {
        foreach ($this->telemetryMeters as $name => $meter) {
            $meter->getAndClear();
        }
    }

    public function markOccurrence(string $name, int $times = 1, array $meters = null): void
    {
        if ($meters !== null) {
            $meters[$name]->markTimes($times);
        } else {
            if (array_key_exists($name, $this->dbMeters)) {
                $this->dbMeters[$name]->markTimes($times);
            }
            if (array_key_exists($name, $this->telemetryMeters)) {
                $this->telemetryMeters[$name]->markTimes($times);
            }
        }
    }

    public function markTelemetryOccurrence(string $name, int $times): void
    {
        $this->markOccurrence($this->telemetryMeters, $name, $times);
    }

    /**
     * Creates a meter for both database and telemetry collection.
     */
    public function createMeter(string $name): void
    {
        $dbMeter = new Meter($name);
        $this->dbMeters[$name] = $dbMeter;
        $telemetryMeter = new Meter($name);
        $this->telemetryMeters[$name] = $telemetryMeter;
    }

    /**
     * Creates a meter only for database collection.
     */
    public function createDbMeter(string $name): void
    {
        $dbMeter = new Meter($name);
        $$this->dbMeters[$name] = $dbMeter;
    }
}
