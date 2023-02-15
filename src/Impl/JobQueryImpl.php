<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Util\{
    ClockUtil,
    CompareUtil,
    EnsureUtil//,
    //ImmutablePair
};
use Jabe\Runtime\{
    JobInterface,
    JobQueryInterface
};

class JobQueryImpl extends AbstractQuery implements JobQueryInterface, \Serializable
{
    protected $activityId;
    protected $id;
    protected $ids = [];
    protected $jobDefinitionId;
    protected $processInstanceId;
    protected $processInstanceIds = [];
    protected $executionId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected bool $retriesLeft = false;
    protected bool $executable = false;
    protected bool $onlyTimers = false;
    protected bool $onlyMessages = false;
    protected $duedateHigherThan;
    protected $duedateLowerThan;
    protected $duedateHigherThanOrEqual;
    protected $duedateLowerThanOrEqual;
    protected $createdBefore;
    protected $createdAfter;
    protected $priorityHigherThanOrEqual;
    protected $priorityLowerThanOrEqual;
    protected bool $withException = false;
    protected $exceptionMessage;
    protected $failedActivityId;
    protected bool $noRetriesLeft = false;
    protected $suspensionState;
    protected bool $isTenantIdSet = false;
    protected $tenantIds = [];
    protected bool $includeJobsWithoutTenantId = false;

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'activityId' => $this->activityId,
            'ids' => $this->ids,
            'jobDefinitionId' => $this->jobDefinitionId,
            'processInstanceId' => $this->processInstanceId,
            'processInstanceIds' => $this->processInstanceIds,
            'executionId' => $this->executionId,
            'processDefinitionId' => $this->processDefinitionId,
            'processDefinitionKey' => $this->processDefinitionKey,
            'retriesLeft' => $this->retriesLeft,
            'executable' => $this->executable,
            'onlyTimers' => $this->onlyTimers,
            'onlyMessages' => $this->onlyMessages,
            'duedateHigherThan' => $this->duedateHigherThan,
            'duedateLowerThan' => $this->duedateLowerThan,
            'duedateHigherThanOrEqual' => $this->duedateHigherThanOrEqual,
            'duedateLowerThanOrEqual' => $this->duedateLowerThanOrEqual,
            'createdBefore' => $this->createdBefore,
            'createdAfter' => $this->createdAfter,
            'priorityHigherThanOrEqual' => $this->priorityHigherThanOrEqual,
            'priorityLowerThanOrEqual' => $this->priorityLowerThanOrEqual,
            'withException' => $this->withException,
            'exceptionMessage' => $this->exceptionMessage,
            'failedActivityId' => $this->failedActivityId,
            'noRetriesLeft' => $this->noRetriesLeft,
            'suspensionState' => serialize($this->suspensionState),
            'isTenantIdSet' => $this->isTenantIdSet,
            'tenantIds' => $this->tenantIds,
            'includeJobsWithoutTenantId' => $this->includeJobsWithoutTenantId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->activityId = $json->activityId;
        $this->ids = $json->ids;
        $this->jobDefinitionId = $json->jobDefinitionId;
        $this->processInstanceId = $json->processInstanceId;
        $this->processInstanceIds = $json->processInstanceIds;
        $this->executionId = $json->executionId;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->processDefinitionKey = $json->processDefinitionKey;
        $this->retriesLeft = $json->retriesLeft;
        $this->executable = $json->executable;
        $this->onlyTimers = $json->onlyTimers;
        $this->onlyMessages = $json->onlyMessages;
        $this->duedateHigherThan = $json->duedateHigherThan;
        $this->duedateLowerThan = $json->duedateLowerThan;
        $this->duedateHigherThanOrEqual = $json->duedateHigherThanOrEqual;
        $this->duedateLowerThanOrEqual = $json->duedateLowerThanOrEqual;
        $this->createdBefore = $json->createdBefore;
        $this->createdAfter = $json->createdAfter;
        $this->priorityHigherThanOrEqual = $json->priorityHigherThanOrEqual;
        $this->priorityLowerThanOrEqual = $json->priorityLowerThanOrEqual;
        $this->withException = $json->withException;
        $this->exceptionMessage = $json->exceptionMessage;
        $this->failedActivityId = $json->failedActivityId;
        $this->noRetriesLeft = $json->noRetriesLeft;
        $this->suspensionState = unserialize($json->suspensionState);
        $this->isTenantIdSet = $json->isTenantIdSet;
        $this->tenantIds = $json->tenantIds;
        $this->includeJobsWithoutTenantId = $json->includeJobsWithoutTenantId;
    }

    public function jobId(?string $jobId): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided job id", "jobId", $jobId);
        $this->id = $jobId;
        return $this;
    }

    public function jobIds(array $ids): JobQueryInterface
    {
        EnsureUtil::ensureNotEmpty("Set of job ids", "ids", $ids);
        $this->ids = $ids;
        return $this;
    }

    public function jobDefinitionId(?string $jobDefinitionId): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided job definition id", "jobDefinitionId", $jobDefinitionId);
        $this->jobDefinitionId = $jobDefinitionId;
        return $this;
    }

    public function processInstanceId(?string $processInstanceId): JobQueryImpl
    {
        EnsureUtil::ensureNotNull("Provided process instance id", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processInstanceIds(array $processInstanceIds): JobQueryInterface
    {
        EnsureUtil::ensureNotEmpty("Set of process instance ids", "processInstanceIds", $processInstanceIds);
        $this->processInstanceIds = $processInstanceIds;
        return $this;
    }

    public function executionId(?string $executionId): JobQueryImpl
    {
        EnsureUtil::ensureNotNull("Provided execution id", "executionId", $executionId);
        $this->executionId = $executionId;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided process definition id", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(?string $processDefinitionKey): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided process instance key", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function activityId(?string $activityId): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided activity id", "activityId", $activityId);
        $this->activityId = $activityId;
        return $this;
    }

    public function withRetriesLeft(): JobQueryInterface
    {
        $this->retriesLeft = true;
        return $this;
    }

    public function executable(): JobQueryInterface
    {
        $this->executable = true;
        return $this;
    }

    public function timers(): JobQueryInterface
    {
        if ($this->onlyMessages) {
            throw new ProcessEngineException("Cannot combine onlyTimers() with onlyMessages() in the same query");
        }
        $this->onlyTimers = true;
        return $this;
    }

    public function messages(): JobQueryInterface
    {
        if ($this->onlyTimers) {
            throw new ProcessEngineException("Cannot combine onlyTimers() with onlyMessages() in the same query");
        }
        $this->onlyMessages = true;
        return $this;
    }

    public function duedateHigherThan(?string $date): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided date", "date", $date);
        $this->duedateHigherThan = $date;
        return $this;
    }

    public function duedateLowerThan(?string $date): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided date", "date", $date);
        $this->duedateLowerThan = $date;
        return $this;
    }

    public function duedateHigherThen(?string $date): JobQueryInterface
    {
        return $this->duedateHigherThan($date);
    }

    public function duedateHigherThenOrEquals(?string $date): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided date", "date", $date);
        $this->duedateHigherThanOrEqual = $date;
        return $this;
    }

    public function duedateLowerThen(?string $date): JobQueryInterface
    {
        return $this->duedateLowerThan($date);
    }

    public function duedateLowerThenOrEquals(?string $date): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided date", "date", $date);
        $this->duedateLowerThanOrEqual = $date;
        return $this;
    }

    public function createdBefore(?string $date): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided date", "date", $date);
        $this->createdBefore = $date;
        return $this;
    }

    public function createdAfter(?string $date): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided date", "date", $date);
        $this->createdAfter = $date;
        return $this;
    }

    public function priorityHigherThanOrEquals(int $priority): JobQueryInterface
    {
        $this->priorityHigherThanOrEqual = $priority;
        return $this;
    }

    public function priorityLowerThanOrEquals(int $priority): JobQueryInterface
    {
        $this->priorityLowerThanOrEqual = $priority;
        return $this;
    }

    public function withException(): JobQueryInterface
    {
        $this->withException = true;
        return $this;
    }

    public function exceptionMessage(?string $exceptionMessage): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided exception message", "exceptionMessage", $exceptionMessage);
        $this->exceptionMessage = $exceptionMessage;
        return $this;
    }

    public function failedActivityId(?string $activityId): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided activity id", "activityId", $activityId);
        $this->failedActivityId = $activityId;
        return $this;
    }

    public function noRetriesLeft(): JobQueryInterface
    {
        $this->noRetriesLeft = true;
        return $this;
    }

    public function active(): JobQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function suspended(): JobQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions()
            || CompareUtil::areNotInAscendingOrder($this->priorityHigherThanOrEqual, $this->priorityLowerThanOrEqual)
            || $this-> hasExcludingDueDateParameters()
            || CompareUtil::areNotInAscendingOrder($this->createdAfter, $this->createdBefore);
    }

    private function hasExcludingDueDateParameters(): bool
    {
        $dueDates = [];
        if ($this->duedateHigherThan !== null && $this->duedateHigherThanOrEqual !== null) {
            $dueDates[] = CompareUtil::min($this->duedateHigherThan, $this->duedateHigherThanOrEqual);
            $dueDates[] = CompareUtil::max($this->duedateHigherThan, $this->duedateHigherThanOrEqual);
        } elseif ($this->duedateHigherThan !== null) {
            $dueDates[] = $this->duedateHigherThan;
        } elseif ($this->duedateHigherThanOrEqual !== null) {
            $dueDates[] = $this->duedateHigherThanOrEqual;
        }
        if ($this->duedateLowerThan !== null && $this->duedateLowerThanOrEqual !== null) {
            $dueDates[] = CompareUtil::min($this->duedateLowerThan, $this->duedateLowerThanOrEqual);
            $dueDates[] = CompareUtil::max($this->duedateLowerThan, $this->duedateLowerThanOrEqual);
        } elseif ($this->duedateLowerThan !== null) {
            $dueDates[] = $this->duedateLowerThan;
        } elseif ($this->duedateLowerThanOrEqual !== null) {
            $dueDates[] = $this->duedateLowerThanOrEqual;
        }
        return CompareUtil::areNotInAscendingOrder($dueDates);
    }

    public function tenantIdIn(array $tenantIds): JobQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): JobQueryInterface
    {
        $this->isTenantIdSet = true;
        $this->tenantIds = null;
        return $this;
    }

    public function includeJobsWithoutTenantId(): JobQueryInterface
    {
        $this->includeJobsWithoutTenantId = true;
        return $this;
    }

    public function orderByJobDuedate(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::duedate());
    }

    public function orderByExecutionId(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::executionId());
    }

    public function orderByJobId(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::jobId());
    }

    public function orderByProcessInstanceId(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::processInstanceId());
    }

    public function orderByProcessDefinitionId(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::processDefinitionId());
    }

    public function orderByProcessDefinitionKey(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::processDefinitionKey());
    }

    public function orderByJobRetries(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::retries());
    }

    public function orderByJobPriority(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::priority());
    }

    public function orderByTenantId(): JobQueryInterface
    {
        return $this->orderBy(JobQueryProperty::tenantId());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getJobManager()
            ->findJobCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getJobManager()
            ->findJobsByQueryCriteria($this, $page);
    }

    public function executeDeploymentIdMappingsList(CommandContext $commandContext): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getJobManager()
            ->findDeploymentIdMappingsByQueryCriteria($this);
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function getRetriesLeft(): bool
    {
        return $this->retriesLeft;
    }

    public function getExecutable(): bool
    {
        return $this->executable;
    }

    public function getNow(): ?string
    {
        return ClockUtil::getCurrentTime()->format('c');
    }

    public function isWithException(): bool
    {
        return $this->withException;
    }

    public function getExceptionMessage(): ?string
    {
        return $this->exceptionMessage;
    }
}
