<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;

class CommandContextFactory
{
    protected $processEngineConfiguration;

    public function createCommandContext(): CommandContext
    {
        return new CommandContext($processEngineConfiguration);
    }

    public function getProcessEngineConfiguration(): ProcessEngineConfigurationImpl
    {
        return $this->processEngineConfiguration;
    }

    public function setProcessEngineConfiguration(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        $this->processEngineConfiguration = $processEngineConfiguration;
    }
}
