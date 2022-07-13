<?php

namespace Jabe\Engine\Impl\Metrics\Reporter;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Metrics\{
    Meter,
    MetricsLogger,
    MetricsRegistry
};
use Jabe\Engine\Impl\Persistence\Entity\MeterLogEntity;
use Jabe\Engine\Impl\Util\ClockUtil;
use Jabe\Engine\Impl\Util\Concurrent\TimerTask;

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

    public function run(): void
    {
        try {
            $this->collectMetrics();
        } catch (\Exception $e) {
            try {
                //LOG.couldNotCollectAndLogMetrics(e);
            } catch (\Exception $ex) {
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
                ClockUtil::getCurrentTime()->format('c')
            );
        }
        $this->commandExecutor->execute(new MetricsCollectionCmd($logs));
    }

    public function getReporter(): ?string
    {
        return $this->reporterId;
    }

    public function setReporter(string $reporterId): void
    {
        $this->reporterId = $reporterId;
    }
}
