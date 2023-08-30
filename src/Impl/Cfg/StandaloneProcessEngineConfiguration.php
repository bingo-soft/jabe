<?php

namespace Jabe\Impl\Cfg;

use Jabe\Impl\Db\Sql\DbSqlSessionFactory;
use Jabe\Impl\Interceptor\{
    CommandContextInterceptor,
    CommandCounterInterceptor,
    LogInterceptor,
    ProcessApplicationContextInterceptor
};

class StandaloneProcessEngineConfiguration extends ProcessEngineConfigurationImpl
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getDefaultCommandInterceptorsTxRequired(): array
    {
        $defaultCommandInterceptorsTxRequired = [];
        if (!$this->isDisableExceptionCode()) {
            $defaultCommandInterceptorsTxRequired[] = $this->getExceptionCodeInterceptor();
        }
        $defaultCommandInterceptorsTxRequired[] = new LogInterceptor();
        $defaultCommandInterceptorsTxRequired[] = new CommandCounterInterceptor($this);
        $defaultCommandInterceptorsTxRequired[] = new ProcessApplicationContextInterceptor($this);
        $defaultCommandInterceptorsTxRequired[] = new CommandContextInterceptor($this->commandContextFactory, $this);
        return $defaultCommandInterceptorsTxRequired;
    }

    protected function getDefaultCommandInterceptorsTxRequiresNew(): array
    {
        $defaultCommandInterceptorsTxRequired = [];
        if (!$this->isDisableExceptionCode()) {
            $defaultCommandInterceptorsTxRequired[] = $this->getExceptionCodeInterceptor();
        }
        $defaultCommandInterceptorsTxRequired[] = new LogInterceptor();
        $defaultCommandInterceptorsTxRequired[] = new CommandCounterInterceptor($this);
        $defaultCommandInterceptorsTxRequired[] = new ProcessApplicationContextInterceptor($this);
        $defaultCommandInterceptorsTxRequired[] = new CommandContextInterceptor($this->commandContextFactory, $this, true);
        return $defaultCommandInterceptorsTxRequired;
    }
}
