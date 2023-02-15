<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Parser\{
    DefaultFailedJobParseListener,
    FailedJobRetryConfiguration
};
use Jabe\Impl\Calendar\DurationHelper;
use Jabe\Impl\Context\Context;
use Jabe\Impl\El\ExpressionInterface;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\{
    AsyncContinuationJobHandler,
    JobExecutorLogger,
    TimerCatchIntermediateEventJobHandler,
    TimerExecuteNestedActivityJobHandler,
    TimerStartEventJobHandler,
    TimerStartEventSubprocessJobHandler
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity,
    ProcessDefinitionEntity
};
use Jabe\Impl\Pvm\Process\ActivityImpl;
use Jabe\Impl\Util\ParseUtil;

class DefaultJobRetryCmd extends JobRetryCmd
{
    public const SUPPORTED_TYPES = [
        TimerExecuteNestedActivityJobHandler::TYPE,
        TimerCatchIntermediateEventJobHandler::TYPE,
        TimerStartEventJobHandler::TYPE,
        TimerStartEventSubprocessJobHandler::TYPE,
        AsyncContinuationJobHandler::TYPE
    ];
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    public function __construct(?string $jobId, \Throwable $exception)
    {
        parent::__construct($jobId, $exception);
    }

    public function execute(CommandContext $commandContext)
    {
        $job = $this->getJob();

        $activity = $this->getCurrentActivity($commandContext, $job);

        if ($activity === null) {
            //LOG.debugFallbackToDefaultRetryStrategy();
            $this->executeStandardStrategy($commandContext);
        } else {
            try {
                $this->executeCustomStrategy($commandContext, $job, $activity);
            } catch (\Exception $e) {
                //LOG.debugFallbackToDefaultRetryStrategy();
                $this->executeStandardStrategy($commandContext);
            }
        }

        return null;
    }

    protected function executeStandardStrategy(CommandContext $commandContext): void
    {
        $job = $this->getJob();
        if ($job !== null) {
            $job->unlock();
            $this->logException($job);
            $this->decrementRetries($job);
            $this->notifyAcquisition($commandContext);
        } else {
            //LOG.debugFailedJobNotFound(jobId);
        }
    }

    protected function executeCustomStrategy(CommandContext $commandContext, JobEntity $job, ActivityImpl $activity): void
    {
        $retryConfiguration = $this->getFailedJobRetryConfiguration($job, $activity);

        if ($retryConfiguration === null) {
            $this->executeStandardStrategy($commandContext);
        } else {
            $isFirstExecution = $this->isFirstJobExecution($job);

            $this->logException($job);

            if ($isFirstExecution) {
                // then change default retries to the ones configured
                $this->initializeRetries($job, $retryConfiguration->getRetries());
            } else {
                //LOG.debugDecrementingRetriesForJob(job.getId());
            }

            $intervals = $retryConfiguration->getRetryIntervals();
            $intervalsCount = count($intervals);
            $indexOfInterval = max(0, min($intervalsCount - 1, $intervalsCount - ($job->getRetries() - 1)));
            $durationHelper = $this->getDurationHelper($intervals[$indexOfInterval]);

            $job->setDuedate($durationHelper->getDateAfter());
            $job->unlock();

            $this->decrementRetries($job);
            $this->notifyAcquisition($commandContext);
        }
    }

    protected function getCurrentActivity(CommandContext $commandContext, JobEntity $job): ?ActivityImpl
    {
        $type = $job->getJobHandlerType();
        $activity = null;

        if (in_array($type, self::SUPPORTED_TYPES)) {
            $deploymentCache = Context::getProcessEngineConfiguration()->getDeploymentCache();
            $processDefinitionEntity =
                $deploymentCache->findDeployedProcessDefinitionById($job->getProcessDefinitionId());
            $activity = $processDefinitionEntity->findActivity($job->getActivityId());
        } else {
            // noop, because activity type is not supported
        }

        return $activity;
    }

    protected function fetchExecutionEntity(?string $executionId): ?ExecutionEntity
    {
        return Context::getCommandContext()
                        ->getExecutionManager()
                        ->findExecutionById($executionId);
    }

    protected function getFailedJobRetryConfiguration(JobEntity $job, ActivityImpl $activity): ?FailedJobRetryConfiguration
    {
        $properties = $activity->getProperties();
        $key = strval(DefaultFailedJobParseListener::$FAILED_JOB_CONFIGURATION);
        $retryConfiguration = null;
        if ($properties->contains($key)) {
            $retryConfiguration = $properties->get($key);
        }

        while ($retryConfiguration !== null && $retryConfiguration->getExpression() !== null) {
            $retryIntervals = $this->getFailedJobRetryTimeCycle($job, $retryConfiguration->getExpression());
            $retryConfiguration = ParseUtil::parseRetryIntervals($retryIntervals);
        }

        return $retryConfiguration;
    }

    protected function getFailedJobRetryTimeCycle(JobEntity $job, ?ExpressionInterface $expression): ?string
    {
        $executionId = $job->getExecutionId();
        $execution = null;

        if ($executionId !== null) {
            $execution = $this->fetchExecutionEntity($executionId);
        }

        $value = null;

        if ($expression === null) {
            return null;
        }

        try {
            $value = $expression->getValue($execution, $execution);
        } catch (\Exception $e) {
            //LOG.exceptionWhileParsingExpression(jobId, e.getCause().getMessage());
        }

        if (is_string($value)) {
            return $value;
        } else {
            // default behavior
            return null;
        }
    }

    protected function getDurationHelper(?string $failedJobRetryTimeCycle): DurationHelper
    {
        return new DurationHelper($failedJobRetryTimeCycle);
    }

    protected function isFirstJobExecution(JobEntity $job): bool
    {
        return $job->getExceptionByteArrayId() === null && $job->getExceptionMessage() === null;
    }

    protected function initializeRetries(JobEntity $job, int $retries): void
    {
        //LOG.debugInitiallyAppyingRetryCycleForJob(job.getId(), retries);
        $job->setRetries($retries);
    }
}
