<?php

namespace Jabe\Engine\Impl\Telemetry\Reporter;

use GuzzleHttp\ClientInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\IsTelemetryEnabledCmd;
use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Engine\Impl\Metrics\{
    Meter,
    MetricsRegistry
};
use Jabe\Engine\Impl\Metrics\Util\{
    MetricsUtil
};
use Jabe\Engine\Impl\Telemetry\{
    CommandCounter,
    TelemetryLogger,
    TelemetryRegistry
};
use Jabe\Engine\Impl\Telemetry\Dto\{
    ApplicationServerImpl,
    CommandImpl,
    TelemetryDataImpl,
    InternalsImpl,
    MetricImpl,
    ProductImpl
};
use Jabe\Engine\Impl\Util\{
    ConnectUtil,
    JsonUtil,
    StringUtil,
    TelemetryUtil
};
use Jabe\Engine\Telemetry\{
    CommandInterface,
    MetricInterface
};
use Jabe\Engine\Impl\Util\Concurrent\{
    RunnableInterface,
    TimerTask
};
use Jabe\Engine\Impl\Util\Core\MediaType;
use Jabe\Engine\Impl\Util\Net\HttpURLConnection;
use Jabe\Engine\Management\Metrics;

class TelemetrySendingTask extends TimerTask
{
    protected const METRICS_TO_REPORT = [
        Metrics::ROOT_PROCESS_INSTANCE_START,
        Metrics::EXECUTED_DECISION_INSTANCES,
        Metrics::EXECUTED_DECISION_ELEMENTS,
        Metrics::ACTIVTY_INSTANCE_START
    ];
    //protected static final TelemetryLogger LOG = ProcessEngineLogger.TELEMETRY_LOGGER;
    protected const UUID4_PATTERN = "/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/i";

    protected $commandExecutor;
    protected $telemetryEndpoint;
    protected $staticData;
    protected $httpConnector;
    protected $telemetryRequestRetries;
    protected $telemetryRegistry;
    protected $metricsRegistry;
    protected $telemetryRequestTimeout;

    public function __construct(
        CommandExecutorInterface $commandExecutor,
        string $telemetryEndpoint,
        int $telemetryRequestRetries,
        TelemetryDataImpl $data,
        ClientInterface $httpConnector,
        TelemetryRegistry $telemetryRegistry,
        MetricsRegistry $metricsRegistry,
        int $telemetryRequestTimeout
    ) {
        $this->commandExecutor = $commandExecutor;
        $this->telemetryEndpoint = $telemetryEndpoint;
        $this->telemetryRequestRetries = $telemetryRequestRetries;
        $this->staticData = $data;
        $this->httpConnector = $httpConnector;
        $this->telemetryRegistry = $telemetryRegistry;
        $this->metricsRegistry = $metricsRegistry;
        $this->telemetryRequestTimeout = $telemetryRequestTimeout;
    }

    public function run(): void
    {
        //LOG.startTelemetrySendingTask();

        if (!isTelemetryEnabled()) {
            //LOG.telemetryDisabled();
            return;
        }

        TelemetryUtil::toggleLocalTelemetry(true, $this->telemetryRegistry, $this->metricsRegistry);

        $scope = $this;
        $this->performDataSend(new class ($scope) implements RunnableInterface {
            private $scope;

            public function __construct($scope)
            {
                $this->scope = $scope;
            }

            public function run(): void
            {
                $this->scope->updateAndSendData(true, true);
            }
        });
    }

    public function updateAndSendData(bool $sendData, bool $addLegacyNames): TelemetryDataImpl
    {
        $this->updateStaticData();
        $dynamicData = $this->resolveDynamicData($sendData, $addLegacyNames);
        $mergedData = new TelemetryDataImpl($this->staticData);
        $mergedData->mergeInternals($dynamicData);

        if ($sendData) {
            try {
                $this->sendData($mergedData);
            } catch (\Exception $e) {
                // so that we send it again the next time
                $this->restoreDynamicData($dynamicData);
                throw $e;
            }
        }
        return $mergedData;
    }

    protected function updateStaticData(): void
    {
        $internals = $this->staticData->getProduct()->getInternals();

        if ($internals->getApplicationServer() === null) {
            $applicationServer = $this->telemetryRegistry->getApplicationServer();
            $internals->setApplicationServer($applicationServer);
        }

        if ($internals->isTelemetryEnabled() === null) {
            $internals->setTelemetryEnabled(true);// this can only be true, otherwise we would not collect data to send
        }

        // license key and Webapps data is fed from the outside to the registry but needs to be constantly updated
        $internals->setLicenseKey($this->telemetryRegistry->getLicenseKey());
        $internals->setWebapps($this->telemetryRegistry->getWebapps());
    }

    protected function isTelemetryEnabled(): bool
    {
        $telemetryEnabled = $this->commandExecutor->execute(new IsTelemetryEnabledCmd());
        return $telemetryEnabled !== null && $telemetryEnabled->booleanValue();
    }

    protected function sendData(TelemetryDataImpl $dataToSend): void
    {
        $telemetryData = JsonUtil::asString($dataToSend);
        $requestParams = ConnectUtil::assembleRequestParameters(
            ConnectUtil::METHOD_NAME_POST,
            $this->telemetryEndpoint,
            MediaType::APPLICATION_JSON,
            $telemetryData
        );
        $requestParams = ConnectUtil::addRequestTimeoutConfiguration($requestParams, $this->telemetryRequestTimeout);

        $response = $this->httpConnector->request(ConnectUtil::METHOD_NAME_POST, $this->telemetryEndpoint, $requestParams);

        if ($response === null) {
            //LOG.unexpectedResponseWhileSendingTelemetryData();
        } else {
            $responseCode = $response->getResponseParameter(ConnectUtil::PARAM_NAME_RESPONSE_STATUS_CODE);

            if ($this->isSuccessStatusCode($responseCode)) {
                if ($responseCode != HttpURLConnection::HTTP_ACCEPTED) {
                    //LOG.unexpectedResponseSuccessCode(responseCode);
                }
                //LOG.telemetrySentSuccessfully();
            } else {
                //throw LOG.unexpectedResponseWhileSendingTelemetryData(responseCode);
            }
        }
    }

    /**
     * @return bool true if status code is 2xx
     */
    protected function isSuccessStatusCode(int $statusCode): bool
    {
        return ($this->statusCode / 100) == 2;
    }

    protected function restoreDynamicData(InternalsImpl $internals): void
    {
        $commands = $internals->getCommands();

        foreach ($commands as $key => $value) {
            $this->telemetryRegistry->markOccurrence($key, $value->getCount());
        }

        if ($this->metricsRegistry !== null) {
            $metrics = $internals->getMetrics();

            foreach (self::METRICS_TO_REPORT as $metricToReport) {
                $metricValue = $metrics[$metricToReport];
                $this->metricsRegistry->markTelemetryOccurrence($metricToReport, $metricValue->getCount());
            }
        }
    }

    protected function resolveDynamicData(bool $reset, bool $addLegacyNames): InternalsImpl
    {
        $result = new InternalsImpl();

        $metrics = $this->calculateMetrics($reset, $addLegacyNames);
        $result->setMetrics($metrics);

        // command counts are modified after the metrics are retrieved, because
        // metric retrieval can fail and resetting the command count is a side effect
        // that we would otherwise have to undo
        $commands = $this->fetchAndResetCommandCounts($reset);
        $result->setCommands($commands);

        return $result;
    }

    protected function fetchAndResetCommandCounts(bool $reset): array
    {
        $commandsToReport = [];
        $originalCounts = $this->telemetryRegistry->getCommands();
        foreach ($originalCounts as $key => $counter) {
            $occurrences = $counter->get($reset);
            $commandsToReport[$key] = new CommandImpl($occurrences);
        }
        return $commandsToReport;
    }

    protected function calculateMetrics(bool $reset, bool $addLegacyNames): array
    {
        $metrics = [];

        if ($this->metricsRegistry !== null) {
            $telemetryMeters = $this->metricsRegistry->getTelemetryMeters();

            foreach (self::METRICS_TO_REPORT as $metricToReport) {
                $value = $telemetryMeters[$metricToReport]->get($reset);

                if ($addLegacyNames) {
                    $metrics[$metricToReport] = new MetricImpl($value);
                }

                // add public names
                $metrics[MetricsUtil::resolvePublicName($metricToReport)] = new MetricImpl($value);
            }
        }

        return $metrics;
    }

    protected function performDataSend(RunnableInterface $runnable): void
    {
        if ($this->validateData($this->staticData)) {
            $triesLeft = $this->telemetryRequestRetries + 1;
            $requestSuccessful = false;
            do {
                try {
                    $triesLeft -= 1;
                    $runnable->run();
                    $requestSuccessful = true;
                } catch (\Exception $e) {
                    //LOG.exceptionWhileSendingTelemetryData(e);
                }
            } while (!$requestSuccessful && $triesLeft > 0);
        } else {
            //LOG.sendingTelemetryDataFails(staticData);
        }
    }

    protected function validateData(TelemetryDataImpl $dataToSend): bool
    {
        // validate product data
        $product = $dataToSend->getProduct();
        $installationId = $dataToSend->getInstallation();
        $edition = $product->getEdition();
        $version = $product->getVersion();
        $name = $product->getName();

        // ensure that data is not null or empty strings
        $validProductData = StringUtil::hasText($name) && StringUtil::hasText($version) && StringUtil::hasText($edition) && StringUtil::hasText($installationId);

        // validate installation id
        if ($validProductData) {
            $validProductData = $validProductData && preg_match(self::UUID4_PATTERN, $installationId);
        }

        return $validProductData;
    }
}
