<?php

namespace Jabe\Engine\Impl\Context;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Application\{
    InvocationContext,
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;

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
