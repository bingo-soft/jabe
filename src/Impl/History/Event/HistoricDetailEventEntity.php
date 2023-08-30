<?php

namespace Jabe\Impl\History\Event;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Util\ClassNameUtil;

class HistoricDetailEventEntity extends HistoryEvent
{
    protected $activityInstanceId;
    protected $taskId;
    protected $timestamp;
    protected $tenantId;
    protected $userOperationId;
    protected bool $initial;

    // getters and setters //////////////////////////////////////////////////////

    public function getActivityInstanceId(): ?string
    {
        return $this->activityInstanceId;
    }

    public function setActivityInstanceId(?string $activityInstanceId): void
    {
        $this->activityInstanceId = $activityInstanceId;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function setTaskId(?string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    public function setTimestamp(?string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getUserOperationId(): ?string
    {
        return $this->userOperationId;
    }

    public function setUserOperationId(?string $userOperationId): void
    {
        $this->userOperationId = $userOperationId;
    }

    public function getRootProcessInstanceId(): ?string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(?string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function delete(): void
    {
        Context::getCommandContext()
            ->getDbEntityManager()
            ->delete($this);
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'eventType' => $this->eventType,
            'executionId' => $this->executionId,
            'processDefinitionId' => $this->processDefinitionId,
            'processInstanceId' => $this->processInstanceId,
            'rootProcessInstanceId' => $this->rootProcessInstanceId,
            'removalTime' => $this->removalTime,
            'activityInstanceId' => $this->activityInstanceId,
            'taskId' => $this->taskId,
            'timestamp' => $this->timestamp,
            'tenantId' => $this->tenantId,
            'userOperationId' => $this->userOperationId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->eventType = $data['eventType'];
        $this->executionId = $data['executionId'];
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->processInstanceId = $data['processInstanceId'];
        $this->rootProcessInstanceId = $data['rootProcessInstanceId'];
        $this->removalTime = $data['removalTime'];
        $this->activityInstanceId = $data['activityInstanceId'];
        $this->taskId = $data['taskId'];
        $this->timestamp = $data['timestamp'];
        $this->tenantId = $data['tenantId'];
        $this->userOperationId = $data['userOperationId'];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[activityInstanceId=" . $this->activityInstanceId
                . ", taskId=" . $this->taskId
                . ", timestamp=" . $this->timestamp
                . ", eventType=" . $this->eventType
                . ", executionId=" . $this->executionId
                . ", processDefinitionId=" . $this->processDefinitionId
                . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
                . ", removalTime=" . $this->removalTime
                . ", processInstanceId=" . $this->processInstanceId
                . ", id=" . $this->id
                . ", tenantId=" . $this->tenantId
                . ", userOperationId=" . $this->userOperationId
                . "]";
    }
}
