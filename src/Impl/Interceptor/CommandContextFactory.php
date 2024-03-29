<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;

class CommandContextFactory
{
    protected $processEngineConfiguration;

    public function createCommandContext(): CommandContext
    {
        return new CommandContext($this->processEngineConfiguration);
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
