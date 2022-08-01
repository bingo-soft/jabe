<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\JobExecutor\HistoryCleanup\{
    HistoryCleanupContext,
    HistoryCleanupHelper,
    HistoryCleanupJobDeclaration,
    HistoryCleanupJobHandler
};
use Jabe\Engine\Impl\Persistence\Entity\{
    JobEntity,
    PropertyChange,
    SuspensionState
};

class HistoryCleanupCmd implements CommandInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public static $HISTORY_CLEANUP_JOB_DECLARATION;

    public const MAX_THREADS_NUMBER = 8;

    private $immediatelyDue;

    public function __construct(bool $immediatelyDue)
    {
        $this->immediatelyDue = $immediatelyDue;
    }

    public function execute(CommandContext $commandContext)
    {
        if (self::$HISTORY_CLEANUP_JOB_DECLARATION === null) {
            self::$HISTORY_CLEANUP_JOB_DECLARATION = new HistoryCleanupJobDeclaration();
        }
        if (!$this->isHistoryCleanupEnabled($commandContext)) {
            throw new BadUserRequestException("History cleanup is disabled for this engine");
        }

        $authorizationManager = $commandContext->getAuthorizationManager();
        $processEngineConfiguration = $commandContext->getProcessEngineConfiguration();

        $authorizationManager->checkCamundaAdmin();

        //validate
        if (!$this->willBeScheduled()) {
            //LOG.debugHistoryCleanupWrongConfiguration();
        }

        //find job instance
        $historyCleanupJobs = $this->getHistoryCleanupJobs();

        $degreeOfParallelism = $processEngineConfiguration->getHistoryCleanupDegreeOfParallelism();
        $minuteChunks = HistoryCleanupHelper::listMinuteChunks($degreeOfParallelism);

        if ($this->shouldCreateJobs($historyCleanupJobs)) {
            $historyCleanupJobs = $this->createJobs($degreeOfParallelism, $minuteChunks);
        } elseif ($this->shouldReconfigureJobs($historyCleanupJobs)) {
            $historyCleanupJobs = $this->reconfigureJobs($historyCleanupJobs, $degreeOfParallelism, $minuteChunks);
        } elseif (shouldSuspendJobs(historyCleanupJobs)) {
            $this->suspendJobs($historyCleanupJobs);
        }

        $this->writeUserOperationLog($commandContext);

        return count($historyCleanupJobs) > 0 ? $historyCleanupJobs[0] : null;
    }

    protected function getHistoryCleanupJobs(): array
    {
        $commandContext = Context::getCommandContext();
        return $commandContext->getJobManager()->findJobsByHandlerType(HistoryCleanupJobHandler::TYPE);
    }

    protected function shouldCreateJobs(array $jobs): bool
    {
        return empty($jobs) && $this->willBeScheduled();
    }

    protected function shouldReconfigureJobs(array $jobs): bool
    {
        return !empty($jobs) && $this->willBeScheduled();
    }

    protected function shouldSuspendJobs(array $jobs): bool
    {
        return !empty($jobs) && !$this->willBeScheduled();
    }

    protected function willBeScheduled(): bool
    {
        $commandContext = Context::getCommandContext();
        return $this->immediatelyDue || HistoryCleanupHelper::isBatchWindowConfigured($commandContext);
    }

    protected function createJobs(int $degreeOfParallelism, array $minuteChunks): array
    {
        $commandContext = Context::getCommandContext();

        $jobManager = $commandContext->getJobManager();

        $this->acquireExclusiveLock($commandContext);

        //check again after lock
        $historyCleanupJobs = $this->getHistoryCleanupJobs();

        if (empty($historyCleanupJobs)) {
            foreach ($minuteChunks as $minuteChunk) {
                $job = $this->createJob($minuteChunk);
                $jobManager->insertAndHintJobExecutor($job);
                $historyCleanupJobs[] = $job;
            }
        }

        return $historyCleanupJobs;
    }

    protected function reconfigureJobs(array $historyCleanupJobs, int $degreeOfParallelism, array $minuteChunks): array
    {
        $commandContext = Context::getCommandContext();
        $jobManager = $commandContext->getJobManager();

        $size = min($degreeOfParallelism, count($historyCleanupJobs));

        for ($i = 0; $i < $size; $i += 1) {
            $historyCleanupJob = $historyCleanupJobs[$i];

            //apply new configuration
            $historyCleanupContext = $this->createCleanupContext($minuteChunks[$i]);

            self::$HISTORY_CLEANUP_JOB_DECLARATION->reconfigure($historyCleanupContext, $historyCleanupJob);

            $newDueDate = self::$HISTORY_CLEANUP_JOB_DECLARATION->resolveDueDate($historyCleanupContext);

            $jobManager->reschedule($historyCleanupJob, $newDueDate);
        }

        $delta = $degreeOfParallelism - count($historyCleanupJobs);
        if ($delta > 0) {
            //create new job, as there are not enough of them
            for ($i = $size; $i < $degreeOfParallelism; $i += 1) {
                $job = $this->createJob($minuteChunks[$i]);
                $jobManager->insertAndHintJobExecutor($job);
                $historyCleanupJobs[] = $job;
            }
        } elseif ($delta < 0) {
            //remove jobs, if there are too much of them
            $i = 0;
            foreach ($historyCleanupJobs as $job) {
                if ($i >= $size) {
                    $jobManager->deleteJob($job);
                }
                $i += 1;
            }
        }
        return $historyCleanupJobs;
    }

    protected function suspendJobs(array $jobs): void
    {
        foreach ($jobs as $job) {
            $job->setSuspensionState(SuspensionState::suspended()->getStateCode());
            $job->setDuedate(null);
        }
    }

    protected function createJob(array $minuteChunk): JobEntity
    {
        $historyCleanupContext = $this->createCleanupContext($minuteChunk);
        return self::$HISTORY_CLEANUP_JOB_DECLARATION->createJobInstance($historyCleanupContext);
    }

    protected function createCleanupContext(array $minuteChunk): HistoryCleanupContext
    {
        $minuteFrom = $minuteChunk[0];
        $minuteTo = $minuteChunk[1];
        return new HistoryCleanupContext($this->immediatelyDue, $minuteFrom, $minuteTo);
    }

    public function writeUserOperationLog(CommandContext $commandContext): void
    {
        $propertyChange = new PropertyChange("immediatelyDue", null, $this->immediatelyDue);
        $commandContext->getOperationLogManager()
            ->logJobOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_CREATE_HISTORY_CLEANUP_JOB,
                null,
                null,
                null,
                null,
                null,
                $propertyChange
            );
    }

    protected function isHistoryCleanupEnabled(CommandContext $commandContext): bool
    {
        return $commandContext->getProcessEngineConfiguration()
            ->isHistoryCleanupEnabled();
    }

    protected function acquireExclusiveLock(CommandContext $commandContext): void
    {
        $propertyManager = $commandContext->getPropertyManager();
        //exclusive lock
        $propertyManager->acquireExclusiveLockForHistoryCleanupJob();
    }

    public function isRetryable(): bool
    {
        return true;
    }
}
