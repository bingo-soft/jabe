<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    ProcessDataContext
};
use Jabe\Impl\JobExecutor\{
    JobExecutorContext,
    JobExecutorLogger,
    JobFailureCollector
};
use Jabe\Impl\Persistence\Entity\{
    JobEntity,
    PropertyChange
};
use Jabe\Impl\Util\EnsureUtil;

class ExecuteJobsCmd implements CommandInterface
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected $jobId;

    protected $jobFailureCollector;

    public function __construct(?string $jobId, JobFailureCollector $jobFailureCollector)
    {
        $this->jobId = $jobId;
        $this->jobFailureCollector = $jobFailureCollector;
    }

    public function __serialize(): array
    {
        return [
            'jobId' => $this->jobId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->jobId = $data['jobId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("jobId", "jobId", $this->jobId);

        $job = $commandContext->getDbEntityManager()->selectById(JobEntity::class, $this->jobId);

        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $identityService = $processEngineConfiguration->getIdentityService();

        $jobExecutorContext = Context::getJobExecutorContext();
        if ($job === null) {
            if ($jobExecutorContext !== null) {
                // CAM-1842
                // Job was acquired but does not exist anymore. This is not a problem.
                // It usually means that the job has been deleted after it was acquired which can happen if the
                // the activity instance corresponding to the job is cancelled.
                //LOG.debugAcquiredJobNotFound(jobId);
                return null;
            } else {
                //throw LOG.jobNotFoundException(jobId);
                throw new \Exception("jobNotFoundException " . $this->jobId);
            }
        }

        $this->jobFailureCollector->setJob($job);

        if ($jobExecutorContext === null) { // if null, then we are not called by the job executor
            foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                $checker->checkUpdateJob($job);
            }
            // write a user operation log since we're not called by the job executor
            $commandContext->getOperationLogManager()->logJobOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_EXECUTE,
                $this->jobId,
                $job->getJobDefinitionId(),
                $job->getProcessInstanceId(),
                $job->getProcessDefinitionId(),
                $job->getProcessDefinitionKey(),
                PropertyChange::emptyChange()
            );
        } else {
            $jobExecutorContext->setCurrentJob($job);

            // if the job is called by the job executor then set the tenant id of the job
            // as authenticated tenant to enable tenant checks
            $tenantId = $job->getTenantId();
            if ($tenantId !== null) {
                $identityService->setAuthentication(null, null, [$tenantId]);
            }
        }

        try {
            // register as command context close lister to intercept exceptions on flush
            $commandContext->registerCommandContextListener($this->jobFailureCollector);
            $commandContext->setCurrentJob($job);
            $job->execute($commandContext, ...$args);
        } catch (\Exception $t) {
            $failedActivityId = Context::getCommandInvocationContext()
                ->getProcessDataContext()
                ->getLatestActivityId();

            $this->jobFailureCollector->setFailedActivityId($failedActivityId);
            throw $t;
        } finally {
            if ($jobExecutorContext !== null) {
                $jobExecutorContext->setCurrentJob(null);
                $identityService->clearAuthentication();
            }
        }
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
