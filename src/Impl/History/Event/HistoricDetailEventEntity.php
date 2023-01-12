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

    public function serialize()
    {
        return json_encode([
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
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->eventType = $json->eventType;
        $this->executionId = $json->executionId;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->processInstanceId = $json->processInstanceId;
        $this->rootProcessInstanceId = $json->rootProcessInstanceId;
        $this->removalTime = $json->removalTime;
        $this->activityInstanceId = $json->activityInstanceId;
        $this->taskId = $json->taskId;
        $this->timestamp = $json->timestamp;
        $this->tenantId = $json->tenantId;
        $this->userOperationId = $json->userOperationId;
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
