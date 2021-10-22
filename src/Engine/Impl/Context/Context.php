<?php

namespace BpmPlatform\Engine\Impl\Context;

use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;

class Context
{
    protected static $commandContextThreadLocal = [];
    protected static $commandInvocationContextThreadLocal = [];

    protected static $processEngineConfigurationStackThreadLocal = [];
    protected static $executionContextStackThreadLocal = [];
    protected static $jobExecutorContextThreadLocal = [];
    protected static $processApplicationContext = [];

    public static function getProcessEngineConfiguration(): ?ProcessEngineConfigurationImpl
    {
        $stack = self::$processEngineConfigurationStackThreadLocal;
        if (empty($stack)) {
            return null;
        }
        return $stack[count($stack) - 1];
    }
}
