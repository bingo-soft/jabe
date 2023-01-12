<?php

namespace Jabe\Impl\History\Event;

use Jabe\Impl\Pvm\Runtime\ActivityInstanceState;
use Jabe\Impl\Util\ClassNameUtil;

class HistoricActivityInstanceEventEntity extends HistoricScopeInstanceEvent
{
    /** the id of the activity */
    protected $activityId;

    /** the name of the activity */
    protected $activityName;

    /** the type of the activity (startEvent, serviceTask ...) */
    protected $activityType;

    /** the id of this activity instance */
    protected $activityInstanceId;

    /** the state of this activity instance */
    protected int $activityInstanceState = 0;

    /** the id of the parent activity instance */
    protected $parentActivityInstanceId;

    /** the id of the child process instance */
    protected $calledProcessInstanceId;

    /** the id of the child case instance */
    protected $calledCaseInstanceId;

    protected $taskId;
    protected $taskAssignee;

    /** id of the tenant which belongs to the activity instance  */
    protected $tenantId;

    // getters and setters //////////////////////////////////////////////////////

    public function getAssignee(): ?string
    {
        return $this->taskAssignee;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function setActivityId(?string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getActivityType(): ?string
    {
        return $this->activityType;
    }

    public function setActivityType(?string $activityType): void
    {
        $this->activityType = $activityType;
    }

    public function getActivityName(): ?string
    {
        return $this->activityName;
    }

    public function setActivityName(?string $activityName): void
    {
        $this->activityName = $activityName;
    }

    public function getActivityInstanceId(): ?string
    {
        return $this->activityInstanceId;
    }

    public function setActivityInstanceId(?string $activityInstanceId): void
    {
        $this->activityInstanceId = $activityInstanceId;
    }

    public function getParentActivityInstanceId(): ?string
    {
        return $this->parentActivityInstanceId;
    }

    public function setParentActivityInstanceId(?string $parentActivityInstanceId): void
    {
        $this->parentActivityInstanceId = $parentActivityInstanceId;
    }

    public function getCalledProcessInstanceId(): ?string
    {
        return $this->calledProcessInstanceId;
    }

    public function setCalledProcessInstanceId(?string $calledProcessInstanceId): void
    {
        $this->calledProcessInstanceId = $calledProcessInstanceId;
    }

    /*public function getCalledCaseInstanceId() {
        return calledCaseInstanceId;
    }

    public void setCalledCaseInstanceId(String calledCaseInstanceId) {
      this.calledCaseInstanceId = calledCaseInstanceId;
    }*/

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function setTaskId(?string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getTaskAssignee(): ?string
    {
        return $this->taskAssignee;
    }

    public function setTaskAssignee(?string $taskAssignee): void
    {
        $this->taskAssignee = $taskAssignee;
    }

    public function setActivityInstanceState(int $activityInstanceState): void
    {
        $this->activityInstanceState = $activityInstanceState;
    }

    public function getActivityInstanceState(): int
    {
        return $this->activityInstanceState;
    }

    public function isCompleteScope(): bool
    {
        return ActivityInstanceState::scopeComplete()->getStateCode() == $this->activityInstanceState;
    }

    public function isCanceled(): bool
    {
        return ActivityInstanceState::canceled()->getStateCode() == $this->activityInstanceState;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getRootProcessInstanceId(): ?string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(?string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function serialize()
    {
        return json_encode([
            'eventType' => $this->eventType,
            'executionId' => $this->executionId,
            'processDefinitionId' => $this->processDefinitionId,
            'processInstanceId' => $this->processInstanceId,
            'rootProcessInstanceId' => $this->rootProcessInstanceId,
            'activityId' => $this->activityId,
            'activityName' => $this->activityName,
            'activityType' => $this->activityType,
            'activityInstanceId' => $this->activityInstanceId,
            'activityInstanceState' => $this->activityInstanceState,
            'calledProcessInstanceId' => $this->calledProcessInstanceId,
            'taskId' => $this->taskId,
            'taskAssignee' => $this->taskAssignee,
            'durationInMillis' => $this->durationInMillis,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'tenantId' => $this->tenantId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->eventType = $json->eventType;
        $this->executionId = $json->executionId;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->processInstanceId = $json->processInstanceId;
        $this->rootProcessInstanceId = $json->rootProcessInstanceId;
        $this->activityId = $json->activityId;
        $this->activityType = $json->activityType;
        $this->activityInstanceId = $json->activityInstanceId;
        $this->activityInstanceState = $json->activityInstanceState;
        $this->calledProcessInstanceId = $json->calledProcessInstanceId;
        $this->taskId = $json->taskId;
        $this->taskAssignee = $json->taskAssignee;
        $this->durationInMillis = $json->durationInMillis;
        $this->startTime = $json->startTime;
        $this->endTime = $json->endTime;
        $this->tenantId = $json->tenantId;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[activityId=" . $this->activityId
                . ", activityName=" . $this->activityName
                . ", activityType=" . $this->activityType
                . ", activityInstanceId=" . $this->activityInstanceId
                . ", activityInstanceState=" . $this->activityInstanceState
                . ", parentActivityInstanceId=" . $this->parentActivityInstanceId
                . ", calledProcessInstanceId=" . $this->calledProcessInstanceId
                //. ", calledCaseInstanceId=" . $this->calledCaseInstanceId
                . ", taskId=" . $this->taskId
                . ", taskAssignee=" . $this->taskAssignee
                . ", durationInMillis=" . $this->durationInMillis
                . ", startTime=" . $this->startTime
                . ", endTime=" . $this->endTime
                . ", eventType=" . $this->eventType
                . ", executionId=" . $this->executionId
                . ", processDefinitionId=" . $this->processDefinitionId
                . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
                . ", processInstanceId=" . $this->processInstanceId
                . ", tenantId=" . $this->tenantId
                . "]";
    }
}
