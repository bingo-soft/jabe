<?php

namespace Tests\Util;

use Jabe\{
    ProcessEngineInterface,
    ProcessEngineConfiguration
};
use Jabe\Test\ProcessEngineRule;

class ProvidedProcessEngineRule extends ProcessEngineRule
{
    protected static $cachedProcessEngine;
    protected $processEngineProvider;

    public function __construct(ProcessEngineBootstrapRule|callable $arg = null)
    {
        if ($arg === null) {
            parent::__construct(self::getOrInitializeCachedProcessEngine(), true);
        } elseif ($arg instanceof ProcessEngineBootstrapRule) {
            parent::__construct(true);
            $this->processEngineProvider = function () use ($arg) {
                return $arg->getProcessEngine();
            };
        } elseif (is_callable($arg)) {
            parent::__construct(true);
            $this->processEngineProvider = $arg;
        }
    }

    protected function initializeProcessEngine(): void
    {
        if ($this->processEngineProvider !== null) {
            $provider = $this->processEngineProvider;
            $this->processEngine = $provider();
        } else {
            parent::initializeProcessEngine();
        }
    }

    protected static function getOrInitializeCachedProcessEngine(): ProcessEngineInterface
    {
        if (self::$cachedProcessEngine == null) {
            $conf = ProcessEngineConfiguration::createProcessEngineConfigurationFromResource("tests/Resources/engine.cfg.xml");
            self::$cachedProcessEngine = $conf->buildProcessEngine();
        }
        return self::$cachedProcessEngine;
    }
}
