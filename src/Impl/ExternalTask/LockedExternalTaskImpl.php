<?php

namespace Jabe\Impl\ExternalTask;

use Jabe\ExternalTask\LockedExternalTaskInterface;
use Jabe\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExternalTaskEntity
};
use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Impl\VariableMapImpl;

class LockedExternalTaskImpl implements LockedExternalTaskInterface
{
    protected $id;
    protected $topicName;
    protected $workerId;
    protected $lockExpirationTime;
    protected $retries;
    protected $errorMessage;
    protected $errorDetails;
    protected $processInstanceId;
    protected $executionId;
    protected $activityId;
    protected $activityInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $processDefinitionVersionTag;
    protected $tenantId;
    protected $priority;
    protected $variables;
    protected $businessKey;
    protected $extensionProperties = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function getWorkerId(): string
    {
        return $this->workerId;
    }

    public function getLockExpirationTime(): string
    {
        return $this->lockExpirationTime;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function getActivityInstanceId(): string
    {
        return $this->activityInstanceId;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function getProcessDefinitionVersionTag(): string
    {
        return $this->processDefinitionVersionTag;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getVariables(): VariableMapInterface
    {
        return $this->variables;
    }

    public function getErrorDetails(): string
    {
        return $this->errorDetails;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function getExtensionProperties(): array
    {
        return $this->extensionProperties;
    }

    /**
     * Construct representation of locked ExternalTask from corresponding entity.
     * During mapping variables will be collected,during collection variables will not be deserialized
     * and scope will not be set to local.
     *
     * @see {@link org.camunda.bpm.engine.impl.core.variable.scope.AbstractVariableScope#collectVariables(VariableMapImpl, Collection, boolean, boolean)}
     *
     * @param externalTaskEntity source persistent entity to use for fields
     * @param variablesToFetch list of variable names to fetch, if null then all variables will be fetched
     * @param isLocal if true only local variables will be collected
     *
     * @return object with all fields copied from the ExternalTaskEntity, error details fetched from the
     * database and variables attached
     */
    public static function fromEntity(ExternalTaskEntity $externalTaskEntity, array $variablesToFetch, bool $isLocal, bool $deserializeVariables, bool $includeExtensionProperties): LockedExternalTaskImpl
    {
        $result = new LockedExternalTaskImpl();
        $result->id = $externalTaskEntity->getId();
        $result->topicName = $externalTaskEntity->getTopicName();
        $result->workerId = $externalTaskEntity->getWorkerId();
        $result->lockExpirationTime = $externalTaskEntity->getLockExpirationTime();
        $result->retries = $externalTaskEntity->getRetries();
        $result->errorMessage = $externalTaskEntity->getErrorMessage();
        $result->errorDetails = $externalTaskEntity->getErrorDetails();

        $result->processInstanceId = $externalTaskEntity->getProcessInstanceId();
        $result->executionId = $externalTaskEntity->getExecutionId();
        $result->activityId = $externalTaskEntity->getActivityId();
        $result->activityInstanceId = $externalTaskEntity->getActivityInstanceId();
        $result->processDefinitionId = $externalTaskEntity->getProcessDefinitionId();
        $result->processDefinitionKey = $externalTaskEntity->getProcessDefinitionKey();
        $result->processDefinitionVersionTag = $externalTaskEntity->getProcessDefinitionVersionTag();
        $result->tenantId = $externalTaskEntity->getTenantId();
        $result->priority = $externalTaskEntity->getPriority();
        $result->businessKey = $externalTaskEntity->getBusinessKey();

        $execution = $externalTaskEntity->getExecution();
        $result->variables = new VariableMapImpl();
        $execution->collectVariables($result->variables, $variablesToFetch, $isLocal, $deserializeVariables);

        if ($includeExtensionProperties) {
            $result->extensionProperties = $execution->getActivity()->getProperty(BpmnProperties::extensionProperties()->getName());
        }
        if ($result->extensionProperties === null) {
            $result->extensionProperties = [];
        }

        return $result;
    }
}
