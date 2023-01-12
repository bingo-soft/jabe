<?php

namespace Jabe\Impl\History\Event;

use Jabe\Impl\Util\ClassNameUtil;

class HistoricProcessInstanceEventEntity extends HistoricScopeInstanceEvent
{
    /** the business key of the process instance */
    protected $businessKey;

    /** the id of the user that started the process instance */
    protected $startUserId;

    /** the id of the super process instance */
    protected $superProcessInstanceId;

    /** the id of the super case instance */
    //protected $superCaseInstanceId;

    /** the reason why this process instance was cancelled (deleted) */
    protected $deleteReason;

    /** id of the activity which ended the process instance */
    protected $endActivityId;

    /** id of the activity which started the process instance */
    protected $startActivityId;

    /** id of the tenant which belongs to the process instance  */
    protected $tenantId;

    protected $state;

    // getters / setters ////////////////////////////////////////

    public function getEndActivityId(): ?string
    {
        return $this->endActivityId;
    }

    public function setEndActivityId(?string $endActivityId): void
    {
        $this->endActivityId = $endActivityId;
    }

    public function getStartActivityId(): ?string
    {
        return $this->startActivityId;
    }

    public function setStartActivityId(?string $startActivityId): void
    {
        $this->startActivityId = $startActivityId;
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function setBusinessKey(?string $businessKey): void
    {
        $this->businessKey = $businessKey;
    }

    public function getStartUserId(): ?string
    {
        return $this->startUserId;
    }

    public function setStartUserId(?string $startUserId): void
    {
        $this->startUserId = $startUserId;
    }

    public function getSuperProcessInstanceId(): ?string
    {
        return $this->superProcessInstanceId;
    }

    public function setSuperProcessInstanceId(?string $superProcessInstanceId): void
    {
        $this->superProcessInstanceId = $superProcessInstanceId;
    }

    /*public String getSuperCaseInstanceId() {
      return superCaseInstanceId;
    }

    public void setSuperCaseInstanceId(String superCaseInstanceId) {
      this.superCaseInstanceId = superCaseInstanceId;
    }*/

    public function getDeleteReason(): ?string
    {
        return $this->deleteReason;
    }

    public function setDeleteReason(?string $deleteReason): void
    {
        $this->deleteReason = $deleteReason;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
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
            'tenantId' => $this->tenantId,
            'businessKey' => $this->businessKey,
            'startUserId' => $this->startUserId,
            'superProcessInstanceId' => $this->superProcessInstanceId,
            'deleteReason' => $this->deleteReason,
            'durationInMillis' => $this->durationInMillis,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'endActivityId' => $this->endActivityId,
            'startActivityId' => $this->startActivityId
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
        $this->tenantId = $json->tenantId;
        $this->businessKey = $json->businessKey;
        $this->startUserId = $json->startUserId;
        $this->superProcessInstanceId = $json->superProcessInstanceId;
        $this->deleteReason = $json->deleteReason;
        $this->durationInMillis = $json->durationInMillis;
        $this->startTime = $json->startTime;
        $this->endTime = $json->endTime;
        $this->endActivityId = $json->endActivityId;
        $this->startActivityId = $json->startActivityId;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
              . "[businessKey=" . $this->businessKey
              . ", startUserId=" . $this->startUserId
              . ", superProcessInstanceId=" . $this->superProcessInstanceId
              . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
              //. ", superCaseInstanceId=" . $this->superCaseInstanceId
              . ", deleteReason=" . $this->deleteReason
              . ", durationInMillis=" . $this->durationInMillis
              . ", startTime=" . $this->startTime
              . ", endTime=" . $this->endTime
              . ", removalTime=" . $this->removalTime
              . ", endActivityId=" . $this->endActivityId
              . ", startActivityId=" . $this->startActivityId
              . ", id=" . $this->id
              . ", eventType=" . $this->eventType
              . ", executionId=" . $this->executionId
              . ", processDefinitionId=" . $this->processDefinitionId
              . ", processInstanceId=" . $this->processInstanceId
              . ", tenantId=" . $this->tenantId
              . "]";
    }
}
