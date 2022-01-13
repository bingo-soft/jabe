<?php

namespace BpmPlatform\Engine\Impl\History\Event;

use BpmPlatform\Engine\Impl\Util\ClassNameUtil;

class HistoricTaskInstanceEventEntity extends HistoricScopeInstanceEvent
{
    protected $taskId;
    protected $assignee;
    protected $owner;
    protected $name;
    protected $description;
    protected $dueDate;
    protected $followUpDate;
    protected $priority;
    protected $parentTaskId;
    protected $deleteReason;
    protected $taskDefinitionKey;
    protected $activityInstanceId;
    protected $tenantId;

    public function getDeleteReason(): string
    {
        return $this->deleteReason;
    }

    public function getAssignee(): string
    {
        return $this->assignee;
    }

    public function setAssignee(string $assignee): void
    {
        $this->assignee = $assignee;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    public function getName(): string
    {
        return $name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    public function setDueDate(string $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function getFollowUpDate(): string
    {
        return $this->followUpDate;
    }

    public function setFollowUpDate(string $followUpDate): void
    {
        $this->followUpDate = $followUpDate;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getParentTaskId(): int
    {
        return $this->parentTaskId;
    }

    public function setParentTaskId(string $parentTaskId): void
    {
        $this->parentTaskId = $parentTaskId;
    }

    public function setDeleteReason(string $deleteReason): void
    {
        $this->deleteReason = $deleteReason;
    }

    public function setTaskId(string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function getTaskDefinitionKey(): string
    {
        return $this->taskDefinitionKey;
    }

    public function setTaskDefinitionKey(string $taskDefinitionKey): void
    {
        $this->taskDefinitionKey = $taskDefinitionKey;
    }

    public function getActivityInstanceId(): string
    {
        return $this->activityInstanceId;
    }

    public function setActivityInstanceId(string $activityInstanceId): void
    {
        $this->activityInstanceId = $activityInstanceId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
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
            'activityInstanceId' => $this->activityInstanceId,
            'taskId' => $this->taskId,
            'tenantId' => $this->tenantId,
            'assignee' => $this->assignee,
            'owner' => $this->owner,
            'name' => $this->name,
            'description' => $this->description,
            'dueDate' => $this->dueDate,
            'followUpDate' => $this->followUpDate,
            'priority' => $this->priority,
            'parentTaskId' => $this->parentTaskId,
            'deleteReason' => $this->deleteReason,
            'taskDefinitionKey' => $this->taskDefinitionKey,
            'durationInMillis' => $this->durationInMillis,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime
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
        $this->activityInstanceId = $json->activityInstanceId;
        $this->taskId = $json->taskId;
        $this->tenantId = $json->tenantId;
        $this->assignee = $json->assignee;
        $this->owner = $json->owner;
        $this->name = $json->name;
        $this->description = $json->description;
        $this->dueDate = $json->dueDate;
        $this->followUpDate = $json->followUpDate;
        $this->priority = $json->priority;
        $this->parentTaskId = $json->parentTaskId;
        $this->deleteReason = $json->deleteReason;
        $this->taskDefinitionKey = $json->taskDefinitionKey;
        $this->durationInMillis = $json->durationInMillis;
        $this->startTime = $json->startTime;
        $this->endTime = $json->endTime;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[taskId" . $this->taskId
                . ", assignee=" . $this->assignee
                . ", owner=" . $this->owner
                . ", name=" . $this->name
                . ", description=" . $this->description
                . ", dueDate=" . $this->dueDate
                . ", followUpDate=" . $this->followUpDate
                . ", priority=" . $this->priority
                . ", parentTaskId=" . $this->parentTaskId
                . ", deleteReason=" . $this->deleteReason
                . ", taskDefinitionKey=" . $this->taskDefinitionKey
                . ", durationInMillis=" . $this->durationInMillis
                . ", startTime=" . $this->startTime
                . ", endTime=" . $this->endTime
                . ", id=" . $this->id
                . ", eventType=" . $this->eventType
                . ", executionId=" . $this->executionId
                . ", processDefinitionId=" . $this->processDefinitionId
                . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
                . ", processInstanceId=" . $this->processInstanceId
                . ", activityInstanceId=" . $this->activityInstanceId
                . ", tenantId=" . $this->tenantId
                . "]";
    }
}
