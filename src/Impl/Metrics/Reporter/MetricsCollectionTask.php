<?php

namespace Jabe\Impl\Metrics\Reporter;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Impl\Metrics\{
    Meter,
    MetricsLogger,
    MetricsRegistry
};
use Jabe\Impl\Persistence\Entity\MeterLogEntity;
use Jabe\Impl\Util\ClockUtil;
use Concurrent\{
    ExecutorServiceInterface,
    ThreadInterface
};
use Concurrent\Task\TimerTask;

class MetricsCollectionTask extends TimerTask
{
    //private final static MetricsLogger LOG = ProcessEngineLogger.METRICS_LOGGER;
    protected $metricsRegistry;
    protected $commandExecutor;
    protected $reporterId = null;

    public function __construct(MetricsRegistry $metricsRegistry, CommandExecutorInterface $commandExecutor)
    {
        $this->metricsRegistry = $metricsRegistry;
        $this->commandExecutor = $commandExecutor;
    }

    public function run(ThreadInterface $process = null, ...$args): void
    {
        try {
            $this->collectMetrics();
        } catch (\Throwable $e) {
            try {
                //LOG.couldNotCollectAndLogMetrics(e);
            } catch (\Throwable $ex) {
                // ignore if log can't be written
            }
        }
    }

    protected function collectMetrics(): void
    {
        $logs = [];
        foreach (array_values($this->metricsRegistry->getDbMeters()) as $meter) {
            $logs[] = new MeterLogEntity(
                $meter->getName(),
                $this->reporterId,
                $meter->getAndClear(),
                ClockUtil::getCurrentTime()->format('Y-m-d H:i:s')
            );
        }
        $this->commandExecutor->execute(new MetricsCollectionCmd($logs));
    }

    public function getReporter(): ?string
    {
        return $this->reporterId;
    }

    public function setReporter(?string $reporterId): void
    {
        $this->reporterId = $reporterId;
    }
}
