<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobDefinitionEntity,
    JobEntity
};
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl
};
use Jabe\Engine\Impl\Util\{
    ClockUtil,
    EnsureUtil
};

abstract class JobDeclaration implements \Serializable
{
    /** the id of the associated persistent jobDefinitionId */
    protected $jobDefinitionId;

    protected $jobHandlerType;
    protected $jobHandlerConfiguration;
    protected $jobConfiguration;

    protected $exclusive = JobEntity::DEFAULT_EXCLUSIVE;

    protected $activity;

    protected $jobPriorityProvider;

    public function __construct(string $jobHandlerType)
    {
        $this->jobHandlerType = $jobHandlerType;
    }

    public function serialize()
    {
        return json_encode([
            'jobDefinitionId' => $this->jobDefinitionId,
            'jobHandlerType' => $this->jobHandlerType,
            'jobHandlerConfiguration' => $this->jobHandlerConfigurationl,
            'jobConfiguration' => $this->jobConfiguration,
            'exclusive' => $this->exclusive
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->jobDefinitionId = $json->jobDefinitionId;
        $this->jobHandlerType = $json->jobHandlerType;
        $this->jobHandlerConfiguration = $json->jobHandlerConfiguration;
        $this->jobConfiguration = $json->jobConfiguration;
        $this->exclusive = $json->exclusive;
    }

    // Job instance factory //////////////////////////////////////////

    /**
     *
     * @return JobEntity the created Job instances
     */
    public function createJobInstance($context = null): JobEntity
    {
        $job = $this->newJobInstance($context);

        // set job definition id
        $jobDefinitionId = $this->resolveJobDefinitionId($context);
        $job->setJobDefinitionId($jobDefinitionId);

        if ($jobDefinitionId !== null) {
            $jobDefinition = Context::getCommandContext()
            ->getJobDefinitionManager()
            ->findById($jobDefinitionId);

            if ($jobDefinition !== null) {
                // if job definition is suspended while creating a job instance,
                // suspend the job instance right away:
                $job->setSuspensionState($jobDefinition->getSuspensionState());
                $job->setProcessDefinitionKey($jobDefinition->getProcessDefinitionKey());
                $job->setProcessDefinitionId($jobDefinition->getProcessDefinitionId());
                $job->setTenantId($jobDefinition->getTenantId());
                $job->setDeploymentId($jobDefinition->getDeploymentId());
            }
        }

        $job->setJobHandlerConfiguration($this->resolveJobHandlerConfiguration($context));
        $job->setJobHandlerType($this->resolveJobHandlerType($context));
        $job->setExclusive($this->resolveExclusive($context));
        $job->setRetries($this->resolveRetries($context));
        $job->setDuedate($this->resolveDueDate($context));

        // contentExecution can be null in case of a timer start event or
        // and batch jobs unrelated to executions
        $contextExecution = $this->resolveExecution($context);

        if (Context::getProcessEngineConfiguration()->isProducePrioritizedJobs()) {
            $priority = Context::getProcessEngineConfiguration()
                ->getJobPriorityProvider()
                ->determinePriority($contextExecution, $this, $jobDefinitionId);

            $job->setPriority($priority);
        }

        if ($contextExecution !== null) {
            // in case of shared process definitions, the job definitions have no tenant id.
            // To distinguish jobs between tenants and enable the tenant check for the job executor,
            // use the tenant id from the execution.
            $job->setTenantId($contextExecution->getTenantId());
        }

        $this->postInitialize($context, $job);

        return $job;
    }

    /**
     * Re-initialize configuration part.
      */
    public function reconfigure($context, JobEntity $job): JobEntity
    {
        return $job;
    }

    /**
     * general callback to override any configuration after the defaults have been applied
     */
    protected function postInitialize($context, JobEntity $job): void
    {
    }

    /**
     * Returns the execution in which context the job is created. The execution
     * is used to determine the job's priority based on a BPMN activity
     * the execution is currently executing. May be null.
     */
    abstract protected function resolveExecution($context): ?ExecutionEntity;

    abstract protected function newJobInstance($context = null): JobEntity;

    // Getter / Setters //////////////////////////////////////////

    public function getJobDefinitionId(): string
    {
        return $this->jobDefinitionId;
    }

    protected function resolveJobDefinitionId($context): string
    {
        return $this->jobDefinitionId;
    }

    public function setJobDefinitionId(string $jobDefinitionId): void
    {
        $this->jobDefinitionId = $jobDefinitionId;
    }

    public function getJobHandlerType(): string
    {
        return $this->jobHandlerType;
    }

    protected function resolveJobHandler(): JobHandlerInterface
    {
        $jobHandlers = Context::getProcessEngineConfiguration()->getJobHandlers();
        $jobHandler = null;
        if (array_keys_exists($this->jobHandlerType, $jobHandlers)) {
            $jobHandler = $jobHandlers[$this->jobHandlerType];
        }
        EnsureUtil::ensureNotNull("Cannot find job handler '" . $this->jobHandlerType . "' from job '" . $this . "'", "jobHandler", $jobHandler);

        return $jobHandler;
    }

    protected function resolveJobHandlerType($context): string
    {
        return $this->jobHandlerType;
    }

    abstract protected function resolveJobHandlerConfiguration($context): JobHandlerConfigurationInterface;

    protected function resolveExclusive($context): bool
    {
        return $this->exclusive;
    }

    protected function resolveRetries($context): int
    {
        return Context::getProcessEngineConfiguration()->getDefaultNumberOfRetries();
    }

    public function resolveDueDate($context): string
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        if ($processEngineConfiguration !== null && ($processEngineConfiguration->isJobExecutorAcquireByDueDate() || $processEngineConfiguration->isEnsureJobDueDateNotNull())) {
            return ClockUtil::getCurrentTime()->format('c');
        } else {
            return null;
        }
    }

    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    public function setExclusive(bool $exclusive): void
    {
        $this->exclusive = $exclusive;
    }

    public function getActivityId(): ?string
    {
        if ($this->activity !== null) {
            return $this->activity->getId();
        } else {
            return null;
        }
    }

    public function getActivity(): ActivityImpl
    {
        return $this->activity;
    }

    public function setActivity(ActivityImpl $activity): void
    {
        $this->activity = $activity;
    }

    public function getProcessDefinition(): ?ProcessDefinitionImpl
    {
        if ($this->activity !== null) {
            return $this->activity->getProcessDefinition();
        } else {
            return null;
        }
    }

    public function getJobConfiguration(): string
    {
        return $this->jobConfiguration;
    }

    public function setJobConfiguration(string $jobConfiguration): void
    {
        $this->jobConfiguration = $jobConfiguration;
    }

    public function getJobPriorityProvider(): ParameterValueProviderInterface
    {
        return $this->jobPriorityProvider;
    }

    public function setJobPriorityProvider(ParameterValueProviderInterface $jobPriorityProvider): void
    {
        $this->jobPriorityProvider = $jobPriorityProvider;
    }
}
