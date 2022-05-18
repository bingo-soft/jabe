<?php

namespace Jabe\Engine\Impl\Telemetry\Reporter;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\IsTelemetryEnabledCmd;
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Metrics\MetricsRegistry;
use Jabe\Engine\Impl\Telemetry\{
    TelemetryLogger,
    TelemetryRegistry
};
use Jabe\Engine\Impl\Telemetry\Dto\TelemetryDataImpl;
use GuzzleHttp\ClientInterface;
use Swoole\Timer;

class TelemetryReporter
{
    //protected static final TelemetryLogger LOG = ProcessEngineLogger.TELEMETRY_LOGGER;

    protected $reportingIntervalInSeconds;
    /**
     * Report after 5 minutes the first time so that we get an initial ping
     * quickly. 5 minutes delay so that other modules (e.g. those collecting the app
     * server name) can contribute their data.
     */
    public const DEFAULT_INIT_REPORT_DELAY_SECONDS = 5 * 60;
    /**
     * Report after 3 hours for the first time so that other modules (e.g. those
     * collecting the app server name) can contribute their data and test cases
     * which accidentally enable reporting are very unlikely to send data.
     */
    public const EXTENDED_INIT_REPORT_DELAY_SECONDS = 3 * 60 * 60;

    protected $telemetrySendingTask;

    protected $commandExecutor;
    protected $telemetryEndpoint;
    protected $telemetryRequestRetries;
    protected $data;
    protected $httpConnector;
    protected $telemetryRegistry;
    protected $metricsRegistry;
    protected $telemetryRequestTimeout;
    protected $timer;

    public function __construct(
        CommandExecutorInterface $commandExecutor,
        string $telemetryEndpoint,
        int $telemetryRequestRetries,
        int $telemetryReportingPeriod,
        TelemetryDataImpl $data,
        ClientInterface $httpConnector,
        TelemetryRegistry $telemetryRegistry,
        MetricsRegistry $metricsRegistry,
        int $telemetryRequestTimeout
    ) {
        $this->commandExecutor = $commandExecutor;
        $this->telemetryEndpoint = $telemetryEndpoint;
        $this->telemetryRequestRetries = $telemetryRequestRetries;
        $this->reportingIntervalInSeconds = $telemetryReportingPeriod;
        $this->data = $data;
        $this->httpConnector = $httpConnector;
        $this->telemetryRegistry = $telemetryRegistry;
        $this->metricsRegistry = $metricsRegistry;
        $this->telemetryRequestTimeout = $telemetryRequestTimeout;
        $this->initTelemetrySendingTask();
    }

    protected function initTelemetrySendingTask(): void
    {
        $this->telemetrySendingTask = new TelemetrySendingTask(
            $this->commandExecutor,
            $this->telemetryEndpoint,
            $this->telemetryRequestRetries,
            $this->data,
            $this->httpConnector,
            $this->telemetryRegistry,
            $this->metricsRegistry,
            $this->telemetryRequestTimeout
        );
    }

    public function start(): void
    {
        if (!$this->isScheduled()) {
            $reportingIntervalInMillis =  $this->reportingIntervalInSeconds * 1000;
            $initialReportingDelay = $this->getInitialReportingDelaySeconds() * 1000;
            try {
                $telemetrySendingTask = $this->telemetrySendingTask;
                $this->timer = Timer::after($initialReportingDelay, function () use ($reportingIntervalInMillis, $telemetrySendingTask) {
                    Timer::tick($reportingIntervalInMillis, function () use ($telemetrySendingTask) {
                        $telemetrySendingTask->run();
                    });
                });
            } catch (\Exception $e) {
                //throw LOG.schedulingTaskFails(e);
                throw $e;
            }
        }
    }

    public function reschedule(): void
    {
        $this->stop(false);
        $this->start();
    }

    public function stop(bool $report = true): void
    {
        if ($this->isScheduled()) {
            // cancel the timer
            Timer::clearAll();
            $this->timer = null;
            if ($report) {
                // collect and send manually for the last time
                $this->reportNow();
            }
        }
    }

    public function reportNow(): void
    {
        if ($this->telemetrySendingTask != null) {
            $this->telemetrySendingTask->run();
        }
    }

    public function isScheduled(): bool
    {
        return $this->timer != null;
    }

    public function getReportingIntervalInSeconds(): int
    {
        return $this->reportingIntervalInSeconds;
    }

    public function getTelemetrySendingTask(): TelemetrySendingTask
    {
        return $this->telemetrySendingTask;
    }

    public function setTelemetrySendingTask(TelemetrySendingTask $telemetrySendingTask): void
    {
        $this->telemetrySendingTask = $telemetrySendingTask;
    }

    public function getTelemetryEndpoint(): string
    {
        return $this->telemetryEndpoint;
    }

    public function getHttpConnector(): ClientInterface
    {
        return $this->httpConnector;
    }

    public function getInitialReportingDelaySeconds(): int
    {
        $enabled = $this->commandExecutor->execute(new IsTelemetryEnabledCmd());
        return $enabled == null ? self::EXTENDED_INIT_REPORT_DELAY_SECONDS : self::DEFAULT_INIT_REPORT_DELAY_SECONDS;
    }
}
