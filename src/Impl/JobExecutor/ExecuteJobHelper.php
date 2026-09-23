<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\OptimisticLockingException;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\ExecuteJobsCmd;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandExecutorInterface,
    ProcessDataContext
};

class ExecuteJobHelper
{
    //private static final JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    public static $LOGGING_HANDLER;

    public function __construct()
    {
        self::ensureLoggingHandlerInitialized();
    }

    private static function ensureLoggingHandlerInitialized(): void
    {
        if (self::$LOGGING_HANDLER === null) {
            self::$LOGGING_HANDLER = new class () implements ExceptionLoggingHandlerInterface {
                public function exceptionWhileExecutingJob(?string $jobId, \Throwable $exception): void
                {
                    // Default behavior, just log exception
                    //LOG.exceptionWhileExecutingJob(jobId, exception);
                }
            };
        }
    }

    public static function loggingHandler(): ExceptionLoggingHandlerInterface
    {
        self::ensureLoggingHandlerInitialized();
        return self::$LOGGING_HANDLER;
    }

    public static function executeJob(
        ?string $nextJobId,
        CommandExecutorInterface $commandExecutor,
        ?JobFailureCollector $jobFailureCollector = null,
        ?CommandInterface $cmd = null,
        ?ProcessEngineConfigurationImpl $configuration = null,
        ...$args
    ): void {
        self::executeJobWithFailurePolicy(
            $nextJobId,
            $commandExecutor,
            $jobFailureCollector,
            $cmd,
            $configuration,
            true,
            ...$args
        );
    }

    public static function executeJobInWorker(
        ?string $nextJobId,
        CommandExecutorInterface $commandExecutor,
        ?JobFailureCollector $jobFailureCollector = null,
        ?CommandInterface $cmd = null,
        ?ProcessEngineConfigurationImpl $configuration = null,
        ...$args
    ): void {
        self::executeJobWithFailurePolicy(
            $nextJobId,
            $commandExecutor,
            $jobFailureCollector,
            $cmd,
            $configuration,
            false,
            ...$args
        );
    }

    protected static function executeJobWithFailurePolicy(
        ?string $nextJobId,
        CommandExecutorInterface $commandExecutor,
        ?JobFailureCollector $jobFailureCollector,
        ?CommandInterface $cmd,
        ?ProcessEngineConfigurationImpl $configuration,
        bool $rethrowHandledJobFailure,
        ...$args
    ): void {
        if ($jobFailureCollector === null) {
            $jobFailureCollector = new JobFailureCollector($nextJobId);
        }
        if ($cmd === null) {
            $cmd = new ExecuteJobsCmd($nextJobId, $jobFailureCollector);
        }

        $jobExecutionFailure = null;
        try {
            $commandExecutor->execute($cmd, ...$args);
        } catch (\Throwable $exception) {
            self::handleJobFailure($nextJobId, $jobFailureCollector, $exception);
            $jobExecutionFailure = $exception;
        } finally {
            // preserve MDC properties before listener invocation and clear MDC for job listener
            $processDataContext = null;
            if ($configuration !== null) {
                $processDataContext = new ProcessDataContext($configuration, true);
                $processDataContext->clearMdc();
            }
            // invoke job listener
            self::invokeJobListener($commandExecutor, $jobFailureCollector);
            /*
            * reset MDC properties after successful listener invocation,
            * in case of an exception in the listener the logging context
            * of the listener is preserved and used from here on
            */
            if ($processDataContext !== null) {
                $processDataContext->updateMdcFromCurrentValues();
            }
        }

        if ($jobExecutionFailure !== null && $rethrowHandledJobFailure) {
            throw $jobExecutionFailure;
        }
    }

    protected static function invokeJobListener(CommandExecutorInterface $commandExecutor, JobFailureCollector $jobFailureCollector): void
    {
        if ($jobFailureCollector->getJobId() !== null) {
            if ($jobFailureCollector->getFailure() !== null) {
                //the failed job listener is responsible for decrementing the retries and logging the exception to the DB.
                $failedJobListener = self::createFailedJobListener($commandExecutor, $jobFailureCollector);

                $exception = self::callFailedJobListenerWithRetries($commandExecutor, $failedJobListener);
                if ($exception !== null) {
                    throw $exception;
                }
            } else {
                $successListener = self::createSuccessfulJobListener($commandExecutor);
                $commandExecutor->execute($successListener);
            }
        }
    }

    /**
     * Calls FailedJobListener, in case of OptimisticLockException retries configured amount of times.
     *
     * @return exception or null if succeeded
     */
    protected static function callFailedJobListenerWithRetries(CommandExecutorInterface $commandExecutor, FailedJobListener $failedJobListener): ?\Throwable
    {
        do {
            try {
                $commandExecutor->execute($failedJobListener);
                return null;
            } catch (\Throwable $ex) {
                $failedJobListener->incrementCountRetries();
            }
        } while ($failedJobListener->getRetriesLeft() > 0);

        return $ex;
    }

    protected static function handleJobFailure(?string $nextJobId, JobFailureCollector $jobFailureCollector, \Throwable $exception): void
    {
        $jobFailureCollector->setFailure($exception);
    }

    protected static function createFailedJobListener(CommandExecutorInterface $commandExecutor, JobFailureCollector $jobFailureCollector): FailedJobListener
    {
        return new FailedJobListener($commandExecutor, $jobFailureCollector);
    }

    protected static function createSuccessfulJobListener(CommandExecutorInterface $commandExecutor): SuccessfulJobListener
    {
        return new SuccessfulJobListener();
    }
}
