<?php

namespace Jabe\Impl\Context;

use Jabe\ProcessEngineException;
use Jabe\Application\{
    InvocationContext,
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Core\Instance\CoreExecution;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInvocationContext
};
use Jabe\Impl\JobExecutor\JobExecutorContext;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;

class Context
{
    protected static $commandContextThreadLocal = [];
    protected static $commandInvocationContextThreadLocal = [];

    protected static $processEngineConfigurationStackThreadLocal = [];
    protected static $executionContextStackThreadLocal = [];
    protected static $jobExecutorContextThreadLocal;
    protected static $processApplicationContext = [];

    public static function getCommandContext(): ?CommandContext
    {
        $stack = self::$commandContextThreadLocal;
        if (empty($stack)) {
            return null;
        }
        return $stack[array_key_first($stack)];
    }

    public static function setCommandContext(CommandContext $commandContext): void
    {
        array_unshift(self::$commandContextThreadLocal, $commandContext);
    }

    public static function removeCommandContext(): void
    {
        array_shift(self::$commandContextThreadLocal);
    }

    public static function getCommandInvocationContext(): CommandInvocationContext
    {
        $stack = self::$commandInvocationContextThreadLocal;
        if (empty($stack)) {
            return null;
        }
        $inv = $stack[array_key_first($stack)];
        return $inv;
    }

    public static function setCommandInvocationContext(CommandInvocationContext $commandInvocationContext): void
    {
        array_unshift(self::$commandInvocationContextThreadLocal, $commandInvocationContext);
    }

    public static function removeCommandInvocationContext(): void
    {
        $stack = self::$commandInvocationContextThreadLocal;
        $currentContext = array_shift(self::$commandInvocationContextThreadLocal);
        if (empty($stack)) {
            // do not clear when called from JobExecutor, will be cleared there after logging
            if (self::getJobExecutorContext() === null) {
                $currentContext->getProcessDataContext()->clearMdc();
            }
        } else {
            // reset the MDC to the logging context of the outer command invocation
            $stack[array_key_first($stack)]->getProcessDataContext()->updateMdcFromCurrentValues();
        }
    }

    public static function getProcessEngineConfiguration(): ?ProcessEngineConfigurationImpl
    {
        $stack = self::$processEngineConfigurationStackThreadLocal;
        if (empty($stack)) {
            return null;
        }
        return $stack[array_key_first($stack)];
    }

    public static function setProcessEngineConfiguration(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        array_unshift(self::$processEngineConfigurationStackThreadLocal, $processEngineConfiguration);
    }

    public static function removeProcessEngineConfiguration(): void
    {
        array_shift(self::$processEngineConfigurationStackThreadLocal);
    }

    /**
     * @deprecated
     */
    public static function getExecutionContext(): ?ExecutionContext
    {
        return self::getBpmnExecutionContext();
    }

    public static function getBpmnExecutionContext(): ?BpmnExecutionContext
    {
        return self::getCoreExecutionContext();
    }

    /*public static CaseExecutionContext getCaseExecutionContext() {
        return (CaseExecutionContext) getCoreExecutionContext();
    }*/

    public static function getCoreExecutionContext(): ?CoreExecutionContext
    {
        $stack = self::$executionContextStackThreadLocal;
        if (empty($stack)) {
            return null;
        } else {
            return $stack[array_key_first($stack)];
        }
    }

    public static function setExecutionContext(ExecutionEntity $execution): void
    {
        if ($execution instanceof ExecutionEntity) {
            array_unshift(self::$executionContextStackThreadLocal, new BpmnExecutionContext($execution));
        }
    }

    public static function removeExecutionContext(): void
    {
        array_shift(self::$executionContextStackThreadLocal);
    }

    public static function getJobExecutorContext(): ?JobExecutorContext
    {
        return self::$jobExecutorContextThreadLocal;
    }

    public static function setJobExecutorContext(JobExecutorContext $jobExecutorContext): void
    {
        self::$jobExecutorContextThreadLocal = $jobExecutorContext;
    }

    public static function removeJobExecutorContext(): void
    {
        self::$jobExecutorContextThreadLocal = null;
    }

    public static function getCurrentProcessApplication(): ?ProcessApplicationReferenceInterface
    {
        $stack = self::$processApplicationContext;
        if (empty($stack)) {
            return null;
        } else {
            return $stack[array_key_first($stack)];
        }
    }

    public static function setCurrentProcessApplication(ProcessApplicationReferenceInterface $reference): void
    {
        array_unshift(self::$processApplicationContext, $reference);
    }

    public static function removeCurrentProcessApplication(): void
    {
        array_shift(self::$processApplicationContext);
    }

    public static function executeWithinProcessApplication($callback, ProcessApplicationReferenceInterface $processApplicationReference, InvocationContext $invocationContext = null)
    {
        $paName = $processApplicationReference->getName();
        try {
            $processApplication = $processApplicationReference->getProcessApplication();
            self::setCurrentProcessApplication($processApplicationReference);

            try {
                // wrap callback
                $wrappedCallback = new ProcessApplicationClassloaderInterceptor($callback);
                // execute wrapped callback
                return $processApplication->execute($wrappedCallback, $invocationContext);
            } catch (\Throwable $e) {
                // unwrap exception
                throw new ProcessEngineException("Unexpected exeption while executing within process application ", $e);
            } finally {
                self::removeCurrentProcessApplication();
            }
        } catch (\Throwable $e) {
            throw new ProcessEngineException("Cannot switch to process application '" . $paName . "' for execution: " . $e->getMessage(), $e);
        }
    }
}
