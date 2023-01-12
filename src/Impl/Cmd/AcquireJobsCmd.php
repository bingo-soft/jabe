<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Page;
use Jabe\Impl\Db\EntityManager\{
    OptimisticLockingListenerInterface,
    OptimisticLockingResult,
};
use Jabe\Impl\Db\EntityManager\Operation\{
    DbEntityOperation,
    DbOperation
};
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\JobExecutor\{
    AcquiredJobs,
    JobExecutor
};
use Jabe\Impl\Persistence\Entity\AcquirableJobEntity;
use Jabe\Impl\Util\ClockUtil;

class AcquireJobsCmd implements CommandInterface, OptimisticLockingListenerInterface
{
    private $jobExecutor;
    protected $acquiredJobs;
    protected int $numJobsToAcquire = 0;

    public function __construct(JobExecutor $jobExecutor, ?int $numJobsToAcquire = null)
    {
        $numJobsToAcquire = $numJobsToAcquire ?? $jobExecutor->getMaxJobsPerAcquisition();
        $this->jobExecutor = $jobExecutor;
        $this->numJobsToAcquire = $numJobsToAcquire;
    }

    public function execute(CommandContext $commandContext)
    {
        $this->acquiredJobs = new AcquiredJobs($this->numJobsToAcquire);

        $jobs = $commandContext
            ->getJobManager()
            ->findNextJobsToExecute(new Page(0, $this->numJobsToAcquire));

        $exclusiveJobsByProcessInstance = [];

        foreach ($jobs as $job) {
            $this->lockJob($job);

            if ($job->isExclusive()) {
                $list = null;
                if (array_key_exists($job->getProcessInstanceId(), $exclusiveJobsByProcessInstance)) {
                    $list = $exclusiveJobsByProcessInstance[$job->getProcessInstanceId()];
                }
                if ($list === null) {
                    $list = [];
                }
                $list[] = $job->getId();
                $exclusiveJobsByProcessInstance[$job->getProcessInstanceId()] = $list;
            } else {
                $this->acquiredJobs->addJobIdBatch($job->getId());
            }
        }

        foreach ($exclusiveJobsByProcessInstance as $jobIds) {
            $this->acquiredJobs->addJobIdBatch($jobIds);
        }

        // register an OptimisticLockingListener which is notified about jobs which cannot be acquired.
        // the listener removes them from the list of acquired jobs.
        $commandContext
            ->getDbEntityManager()
            ->registerOptimisticLockingListener($this);

        return $this->acquiredJobs;
    }

    /**
     * The AcquireJobs command only executes internal code, so we are certain that
     * a retry of failed job locks will not impact user data, and may be performed
     * multiple times.
     */
    public function isRetryable(): bool
    {
        return true;
    }

    protected function lockJob(AcquirableJobEntity $job): void
    {
        $lockOwner = $this->jobExecutor->getLockOwner();
        $job->setLockOwner($lockOwner);

        $lockTimeInMillis = $this->jobExecutor->getLockTimeInMillis();

        $date = new \DateTime(ClockUtil::getCurrentTime()->format('c'));
        $date->modify('+ ' . $lockTimeInMillis . ' milliseconds');
        $job->setLockExpirationTime($date->format('c'));
    }

    public function getEntityType(): ?string
    {
        return AcquirableJobEntity::class;
    }

    public function failedOperation(DbOperation $operation): ?string
    {
        if ($operation instanceof DbEntityOperation) {
            $entityOperation = $operation;

            // could not lock the job -> remove it from list of acquired jobs
            $this->acquiredJobs->removeJobId($entityOperation->getEntity()->getId());

            // When the job that failed the lock with an OLE is removed,
            // we suppress the OLE.
            return OptimisticLockingResult::IGNORE;
        }

        // If none of the conditions are satisfied, this might indicate a bug,
        // so we throw the OLE.
        return OptimisticLockingResult::THROW;
    }
}
