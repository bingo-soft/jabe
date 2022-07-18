<?php

namespace Jabe\Engine\Impl\Metrics\Reporter;

use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Metrics\MetricsRegistry;
use Swoole\Timer;

class DbMetricsReporter
{
    protected $metricsRegistry;
    protected $commandExecutor;
    protected $reporterId;
    // log every 15 minutes...
    protected $reportingIntervalInSeconds = 60 * 15;
    protected $metricsCollectionTask;
    private $timer;

    public function __construct(MetricsRegistry $metricsRegistry, CommandExecutorInterface $commandExecutor)
    {
        $this->metricsRegistry = $metricsRegistry;
        $this->commandExecutor = $commandExecutor;
        $this->initMetricsCollectionTask();
    }

    protected function initMetricsCollectionTask(): void
    {
        $this->metricsCollectionTask = new MetricsCollectionTask($this->metricsRegistry, $this->commandExecutor);
    }

    public function start(): void
    {
        $reportingIntervalInMillis = $this->reportingIntervalInSeconds * 1000;

        $metricsCollectionTask = $this->metricsCollectionTask;

        $this->timer = Timer::tick($reportingIntervalInMillis, function () use ($metricsCollectionTask) {
            $metricsCollectionTask->run();
        });
    }

    public function stop(): void
    {
        if ($this->timer !== null) {
            // cancel the timer
            Timer::clearAll();
            $this->timer = null;
            // collect and log manually for the last time
            $this->reportNow();
        }
    }

    public function reportNow(): void
    {
        if ($this->metricsCollectionTask !== null) {
            $this->metricsCollectionTask->run();
        }
    }

    public function reportValueAtOnce(string $name, int $value): void
    {
        $this->commandExecutor->execute(new ReportDbMetricsValueCmd($this->$reporterId, $name, $value));
    }

    public function getReportingIntervalInSeconds(): int
    {
        return $this->reportingIntervalInSeconds;
    }

    public function setReportingIntervalInSeconds(int $reportingIntervalInSeconds): void
    {
        $this->reportingIntervalInSeconds = $reportingIntervalInSeconds;
    }

    public function getMetricsRegistry(): MetricsRegistry
    {
        return $this->metricsRegistry;
    }

    public function getCommandExecutor(): CommandExecutorInterface
    {
        return $this->commandExecutor;
    }

    public function getMetricsCollectionTask(): MetricsCollectionTask
    {
        return $this->metricsCollectionTask;
    }

    public function setMetricsCollectionTask(MetricsCollectionTask $metricsCollectionTask): void
    {
        $this->metricsCollectionTask = $metricsCollectionTask;
    }

    public function setReporterId(string $reporterId): void
    {
        $this->reporterId = $reporterId;
        if ($this->metricsCollectionTask !== null) {
            $this->metricsCollectionTask->setReporter($reporterId);
        }
    }
}
