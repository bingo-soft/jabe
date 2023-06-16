<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\{
    Direction,
    JobQueryImpl,
    JobQueryProperty,
    Page,
    QueryOrderingProperty
};
use Jabe\Impl\Cfg\{
    ProcessEngineConfigurationImpl,
    TransactionListenerInterface,
    TransactionState
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\Sql\DbSqlSessionFactory;
use Jabe\Impl\JobExecutor\{
    ExclusiveJobAddedNotification,
    JobExecutor,
    JobExecutorContext,
    MessageAddedNotification,
    TimerCatchIntermediateEventJobHandler,
    TimerExecuteNestedActivityJobHandler,
    TimerEventJobHandler,
    TimerStartEventJobHandler,
    TimerStartEventSubprocessJobHandler
};
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Impl\Util\{
    ClockUtil,
    CollectionUtil,
    EnsureUtil//,
    //ImmutablePair
};

class JobManager extends AbstractManager
{
    public static $JOB_PRIORITY_ORDERING_PROPERTY;
    public static $JOB_TYPE_ORDERING_PROPERTY;
    public static $JOB_DUEDATE_ORDERING_PROPERTY;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        if (self::$JOB_PRIORITY_ORDERING_PROPERTY === null) {
            self::$JOB_PRIORITY_ORDERING_PROPERTY = new QueryOrderingProperty(null, JobQueryProperty::priority());
            self::$JOB_TYPE_ORDERING_PROPERTY = new QueryOrderingProperty(null, JobQueryProperty::type());
            self::$JOB_DUEDATE_ORDERING_PROPERTY = new QueryOrderingProperty(null, JobQueryProperty::duedate());

            self::$JOB_PRIORITY_ORDERING_PROPERTY->setDirection(Direction::descending());
            self::$JOB_TYPE_ORDERING_PROPERTY->setDirection(Direction::descending());
            self::$JOB_DUEDATE_ORDERING_PROPERTY->setDirection(Direction::ascending());
        }
    }

    public function updateJob(JobEntity $job): void
    {
        $this->getDbEntityManager()->merge($job);
    }

    public function insertJob(JobEntity $job, ...$args): void
    {
        if (empty($args)) {
            $args = $this->jobExecutorState;
        }
        $job->setCreateTime(ClockUtil::getCurrentTime(...$args)->format('Y-m-d H:i:s'));

        $this->getDbEntityManager()->insert($job, ...$args);
        $this->getHistoricJobLogManager()->fireJobCreatedEvent($job);
    }

    public function deleteJob(JobEntity $job, ?bool $fireDeleteEvent = true): void
    {
        $this->getDbEntityManager()->delete($job);

        if ($fireDeleteEvent) {
            $this->getHistoricJobLogManager()->fireJobDeletedEvent($job);
        }
    }

    public function insertAndHintJobExecutor(JobEntity $jobEntity): void
    {
        $jobEntity->insert();
        if (Context::getProcessEngineConfiguration()->isHintJobExecutor()) {
            $this->hintJobExecutor($jobEntity);
        }
    }

    public function send(MessageEntity $message): void
    {
        $message->insert();
        if (Context::getProcessEngineConfiguration()->isHintJobExecutor()) {
            $this->hintJobExecutor($message);
        }
    }

    public function schedule(TimerEntity $timer, ...$args): void
    {
        $duedate = $timer->getDuedate();
        EnsureUtil::ensureNotNull("duedate", "duedate", $duedate);
        $timer->insert(...$args);
        $this->hintJobExecutorIfNeeded($timer, $duedate, ...$args);
    }

    public function reschedule(JobEntity $jobEntity, ?string $newDuedate): void
    {
        $jobEntity->init(Context::getCommandContext(), true);
        $jobEntity->setSuspensionState(SuspensionState::active()->getStateCode());
        $jobEntity->setDuedate($newDuedate);
        $this->hintJobExecutorIfNeeded($jobEntity, $newDuedate);
    }

    private function hintJobExecutorIfNeeded(JobEntity $jobEntity, ?string $duedate, ...$args): void
    {
        // Check if this timer fires before the next time the job executor will check for new timers to fire.
        // This is highly unlikely because normally waitTimeInMillis is 5000 (5 seconds)
        // and timers are usually set further in the future
        //$jobExecutor = Context::getProcessEngineConfiguration()->getJobExecutor();
        //$waitTimeInMillis = $jobExecutor->getWaitTimeInMillis();
        $waitTimeInMillis = JobExecutor::WAIT_TIME_IN_MILLIS;
        $duedateUt = (new \DateTime($duedate))->getTimestamp() * 1000;
        $currentDateUt = ClockUtil::getCurrentTime(...$args)->getTimestamp() * 1000 + $waitTimeInMillis;
        if ($duedateUt < $currentDateUt) {
            $this->hintJobExecutor($jobEntity, ...$args);
        }
    }

    protected function hintJobExecutor(JobEntity $job, ...$args): void
    {
        $jobExecutor = Context::getProcessEngineConfiguration()->getJobExecutor();
        if ($jobExecutor !== null && !$jobExecutor->isActive()) {
            return;
        }
        if (!empty($this->jobExecutorState) && $this->jobExecutorState[0] instanceof \Swoole\Atomic && !$this->jobExecutorState[0]->get()) {
            return;
        }

        $jobExecutorContext = Context::getJobExecutorContext();
        $transactionListener = null;
        if ($this->isJobPriorityInJobExecutorPriorityRange($job->getPriority())) {
            // add job to be executed in the current processor
            if (
                !$job->isSuspended()
                && $job->isExclusive()
                && $this->isJobDue($job)
                && $jobExecutorContext !== null
                && $jobExecutorContext->isExecutingExclusiveJob()
                && $this->areInSameProcessInstance($job, $jobExecutorContext->getCurrentJob())
            ) {
                // lock job & add to the queue of the current processor
                $currentTime = ClockUtil::getCurrentTime(...$args);
                $currentTimeUt = $currentTime->getTimestamp() * 1000;
                $job->setLockExpirationTime($currentTimeUt + $jobExecutor->getLockTimeInMillis());
                $job->setLockOwner($jobExecutor->getLockOwner());
                $transactionListener = new ExclusiveJobAddedNotification($job->getId(), $jobExecutorContext);
            } else {
                // reset Acquisition strategy and notify the JobExecutor that
                // a new Job is available for execution on future runs
                $transactionListener = new MessageAddedNotification($jobExecutor);
            }
            Context::getCommandContext()
            ->getTransactionContext()
            ->addTransactionListener(TransactionState::COMMITTED, $transactionListener);
        }
    }

    protected function areInSameProcessInstance(JobEntity $job1, JobEntity $job2): bool
    {
        if ($job1 === null || $job2 === null) {
            return false;
        }

        $instance1 = $job1->getProcessInstanceId();
        $instance2 = $job2->getProcessInstanceId();

        return $instance1 !== null && $instance1 == $instance2;
    }

    protected function isJobPriorityInJobExecutorPriorityRange(int $jobPriority): bool
    {
        $configuration = Context::getProcessEngineConfiguration();
        $jobExecutorPriorityRangeMin = $configuration->getJobExecutorPriorityRangeMin();
        $jobExecutorPriorityRangeMax = $configuration->getJobExecutorPriorityRangeMax();
        return ($jobExecutorPriorityRangeMin === null || $jobExecutorPriorityRangeMin <= $jobPriority)
            && ($jobExecutorPriorityRangeMax === null || $jobExecutorPriorityRangeMax >= $jobPriority);
    }

    public function cancelTimers(ExecutionEntity $execution): void
    {
        $timers = Context::getCommandContext()
            ->getJobManager()
            ->findTimersByExecutionId($execution->getId());

        foreach ($timers as $timer) {
            $timer->delete();
        }
    }

    public function findJobById(?string $jobId): ?JobEntity
    {
        return $this->getDbEntityManager()->selectOne("selectJob", $jobId);
    }

    public function findNextJobsToExecute(Page $page): array
    {
        $engineConfiguration = Context::getProcessEngineConfiguration();

        $params = [];
        $now = ClockUtil::getCurrentTime(...$this->jobExecutorState)->format('Y-m-d H:i:s');
        $params["now"] = $now;
        $params["alwaysSetDueDate"] = $this->isEnsureJobDueDateNotNull();
        $params["deploymentAware"] = $engineConfiguration->isJobExecutorDeploymentAware();
        if ($engineConfiguration->isJobExecutorDeploymentAware()) {
            $registeredDeployments = $engineConfiguration->getRegisteredDeployments();
            if (!empty($registeredDeployments)) {
                $params["deploymentIds"] = $registeredDeployments;
            }
        }

        $params["jobPriorityMin"] = $engineConfiguration->getJobExecutorPriorityRangeMin();
        $params["jobPriorityMax"] = $engineConfiguration->getJobExecutorPriorityRangeMax();

        $params["historyCleanupEnabled"] = $engineConfiguration->isHistoryCleanupEnabled();

        $orderingProperties = [];
        if ($engineConfiguration->isJobExecutorAcquireByPriority()) {
            $orderingProperties[] = self::$JOB_PRIORITY_ORDERING_PROPERTY;
        }
        if ($engineConfiguration->isJobExecutorPreferTimerJobs()) {
            $orderingProperties[] = self::$JOB_TYPE_ORDERING_PROPERTY;
        }
        if ($engineConfiguration->isJobExecutorAcquireByDueDate()) {
            $orderingProperties[] = self::$JOB_DUEDATE_ORDERING_PROPERTY;
        }

        $params["orderingProperties"] = $orderingProperties;
        // don't apply default sorting
        $params["applyOrdering"] = !empty($orderingProperties);
        return $this->getDbEntityManager()->selectList("selectNextJobsToExecute", $params, $page);
    }

    public function findJobsByExecutionId(?string $executionId): array
    {
        return $this->getDbEntityManager()->selectList("selectJobsByExecutionId", $executionId);
    }

    public function findJobsByProcessInstanceId(?string $processInstanceId): array
    {
        $jobs = $this->getDbEntityManager()->selectList("selectJobsByProcessInstanceId", $processInstanceId);
        return $job;
    }

    public function findJobsByJobDefinitionId(?string $jobDefinitionId): array
    {
        return $this->getDbEntityManager()->selectList("selectJobsByJobDefinitionId", $jobDefinitionId);
    }

    public function findJobsByHandlerType(?string $handlerType): array
    {
        return $this->getDbEntityManager()->selectList("selectJobsByHandlerType", $handlerType);
    }

    public function findUnlockedTimersByDuedate(?string $duedate, ?Page $page): array
    {
        $query = "selectUnlockedTimersByDuedate";
        return $this->getDbEntityManager()->selectList($query, $duedate, $page);
    }

    public function findTimersByExecutionId(?string $executionId): array
    {
        return $this->getDbEntityManager()->selectList("selectTimersByExecutionId", $executionId);
    }

    public function findJobsByQueryCriteria(JobQueryImpl $jobQuery, ?Page $page): array
    {
        $this->configureQuery($jobQuery);
        return $this->getDbEntityManager()->selectList("selectJobByQueryCriteria", $jobQuery, $page);
    }

    public function findDeploymentIdMappingsByQueryCriteria(JobQueryImpl $jobQuery): array
    {
        $this->configureQuery($jobQuery);
        $processInstanceIds = $jobQuery->getProcessInstanceIds();
        if (!empty($processInstanceIds)) {
            $partitions = CollectionUtil::partition($processInstanceIds, DbSqlSessionFactory::MAXIMUM_NUMBER_PARAMS);
            $result = [];
            foreach ($partitions as $partition) {
                $jobQuery->processInstanceIds([$partition]);
                $result = array_merge($result, $this->getDbEntityManager()->selectList("selectJobDeploymentIdMappingsByQueryCriteria", $jobQuery));
            }
            return $result;
        } else {
            return $this->getDbEntityManager()->selectList("selectJobDeploymentIdMappingsByQueryCriteria", $jobQuery);
        }
    }

    public function findJobsByConfiguration(?string $jobHandlerType, ?string $jobHandlerConfiguration, ?string $tenantId): array
    {
        $params = [];
        $params["handlerType"] = $jobHandlerType;
        $params["handlerConfiguration"] = $jobHandlerConfiguration;
        $params["tenantId"] = $tenantId;

        if (
            TimerCatchIntermediateEventJobHandler::TYPE == $jobHandlerType
            || TimerExecuteNestedActivityJobHandler::TYPE == $jobHandlerType
            || TimerStartEventJobHandler::TYPE == $jobHandlerType
            || TimerStartEventSubprocessJobHandler::TYPE == $jobHandlerType
        ) {
            $queryValue = $jobHandlerConfiguration . TimerEventJobHandler::JOB_HANDLER_CONFIG_PROPERTY_DELIMITER . TimerEventJobHandler::JOB_HANDLER_CONFIG_PROPERTY_FOLLOW_UP_JOB_CREATED;
            $params["handlerConfigurationWithFollowUpJobCreatedProperty"] = $queryValue;
        }

        return $this->getDbEntityManager()->selectList("selectJobsByConfiguration", $params);
    }

    public function findJobCountByQueryCriteria(JobQueryImpl $jobQuery): int
    {
        $this->configureQuery($jobQuery);
        return $this->getDbEntityManager()->selectOne("selectJobCountByQueryCriteria", $jobQuery);
    }

    public function updateJobSuspensionStateById(?string $jobId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["jobId"] = $jobId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateJobSuspensionStateByJobDefinitionId(?string $jobDefinitionId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["jobDefinitionId"] = $jobDefinitionId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateJobSuspensionStateByProcessInstanceId(?string $processInstanceId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateJobSuspensionStateByProcessDefinitionId(?string $processDefinitionId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionId"] = $processDefinitionId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateStartTimerJobSuspensionStateByProcessDefinitionId(?string $processDefinitionId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionId"] = $processDefinitionId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $parameters["handlerType"] = TimerStartEventJobHandler::TYPE;
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateJobSuspensionStateByProcessDefinitionKey(?string $processDefinitionKey, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = false;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateJobSuspensionStateByProcessDefinitionKeyAndTenantId(?string $processDefinitionKey, ?string $processDefinitionTenantId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = true;
        $parameters["processDefinitionTenantId"] = $processDefinitionTenantId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateStartTimerJobSuspensionStateByProcessDefinitionKey(?string $processDefinitionKey, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = false;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $parameters["handlerType"] = TimerStartEventJobHandler::TYPE;
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateStartTimerJobSuspensionStateByProcessDefinitionKeyAndTenantId(?string $processDefinitionKey, ?string $processDefinitionTenantId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = true;
        $parameters["processDefinitionTenantId"] = $processDefinitionTenantId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $parameters["handlerType"] = TimerStartEventJobHandler::TYPE;
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateFailedJobRetriesByJobDefinitionId(?string $jobDefinitionId, int $retries): void
    {
        $parameters = [];
        $parameters["jobDefinitionId"] = $jobDefinitionId;
        $parameters["retries"] = $retries;
        $this->getDbEntityManager()->update(JobEntity::class, "updateFailedJobRetriesByParameters", $parameters);
    }

    public function updateJobPriorityByDefinitionId(?string $jobDefinitionId, int $priority): void
    {
        $parameters = [];
        $parameters["jobDefinitionId"] = $jobDefinitionId;
        $parameters["priority"] = $priority;
        $this->getDbEntityManager()->update(JobEntity::class, "updateJobPriorityByDefinitionId", $parameters);
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getAuthorizationManager()->configureJobQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    protected function configureParameterizedQuery($parameter): ListQueryParameterObject
    {
        return $this->getTenantManager()->configureQuery($parameter);
    }

    protected function isEnsureJobDueDateNotNull(): bool
    {
        return Context::getProcessEngineConfiguration()->isEnsureJobDueDateNotNull();
    }

    /**
     * Sometimes we get a notification of a job that is not yet due, so we
     * should not execute it immediately
     */
    protected function isJobDue(JobEntity $job): bool
    {
        $duedate = $job->getDuedate();
        $now = ClockUtil::getCurrentTime(...$this->jobExecutorState);

        $duedateUt = (new \DateTime($duedate))->getTimestamp();
        $nowUt = $now->getTimestamp();

        return $duedate === null || $duedateUt <= $nowUt;
    }
}
