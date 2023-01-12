<?php

namespace Tests\Util;

use Jabe\{
    ProcessEngineInterface,
    ProcessEngineConfiguration,
    ProcessEngines
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\JobEntity;
use Jabe\Runtime\JobInterface;

class ProcessEngineBootstrapRule
{
    private $processEngine;
    protected $processEngineConfigurator;

    public function __construct($conf = null, $processEngineConfigurator = null)
    {
        if ($conf === null) {
            $conf = 'engine.cfg.xml';
            $this->processEngine = $this->bootstrapEngine($conf);
        } elseif (is_string($conf)) {
            $this->processEngine = $this->bootstrapEngine($conf);
        } elseif (is_callable($processEngineConfigurator) || is_object($processEngineConfigurator)) {
            $this->processEngineConfigurator = $processEngineConfigurator;
            $this->processEngine = $this->bootstrapEngine($conf ?? 'engine.cfg.xml');
        } elseif (is_callable($conf) || is_object($conf)) {
            $this->processEngineConfigurator = $conf;
            $this->processEngine = $this->bootstrapEngine('engine.cfg.xml');
        }
    }

    public function bootstrapEngine(?string $configurationResource): ProcessEngineInterface
    {
        $processEngineConfiguration = ProcessEngineConfiguration::createProcessEngineConfigurationFromResource($configurationResource);
        $this->configureEngine($processEngineConfiguration);
        return $processEngineConfiguration->buildProcessEngine();
    }

    public function configureEngine(ProcessEngineConfigurationImpl $configuration): ProcessEngineConfiguration
    {
        if ($this->processEngineConfigurator !== null) {
            if (is_callable($this->processEngineConfigurator)) {
                return $this->processEngineConfigurator($configuration);
            } elseif (is_object($this->processEngineConfigurator) && method_exists($this->processEngineConfigurator, 'accept')) {
                return $this->processEngineConfigurator->accept($configuration);
            }
        }
        return $configuration;
    }

    public function getProcessEngine(): ProcessEngineInterface
    {
        return $this->processEngine;
    }

    protected function finished(): void
    {
        $this->deleteHistoryCleanupJob();
        $this->processEngine->close();
        ProcessEngines::unregister($this->processEngine);
        $this->processEngine = null;
    }

    private function deleteHistoryCleanupJob(): void
    {
        $jobs = $this->processEngine->getHistoryService()->findHistoryCleanupJobs();
        foreach ($jobs as $job) {
            $this->processEngine->getProcessEngineConfiguration()->getCommandExecutorTxRequired()->execute(new class ($job) implements CommandInterface {
                private $job;

                public function __construct(JobInterface $job)
                {
                    $this->job = $job;
                }

                public function execute(CommandContext $commandContext)
                {
                    $commandContext->getJobManager()->deleteJob($this->job);
                    $commandContext->getHistoricJobLogManager()->deleteHistoricJobLogByJobId($this->job->getId());
                    return null;
                }

                public function isRetryable(): bool
                {
                    return false;
                }
            });
        }
    }
}
