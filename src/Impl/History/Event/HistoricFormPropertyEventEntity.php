<?php

namespace Jabe\Impl\History\Event;

use Jabe\Impl\Util\ClassNameUtil;

class HistoricFormPropertyEventEntity extends HistoricDetailEventEntity
{
    protected $propertyId;
    protected $propertyValue;

    public function getPropertyId(): ?string
    {
        return $this->propertyId;
    }

    public function setPropertyId(?string $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    public function getPropertyValue()
    {
        return $this->propertyValue;
    }

    public function setPropertyValue(?string $propertyValue): void
    {
        $this->propertyValue = $propertyValue;
    }

    public function getTime(): ?string
    {
        return $this->timestamp;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'eventType' => $this->eventType,
            'executionId' => $this->executionId,
            'processDefinitionId' => $this->processDefinitionId,
            'processInstanceId' => $this->processInstanceId,
            'propertyId' => $this->propertyId,
            'propertyValue' => $this->propertyValue,
            'activityInstanceId' => $this->activityInstanceId,
            'taskId' => $this->taskId,
            'tenantId' => $this->tenantId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->eventType = $data['eventType'];
        $this->executionId = $data['executionId'];
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->processInstanceId = $data['processInstanceId'];
        $this->propertyId = $data['propertyId'];
        $this->propertyValue = $data['propertyValue'];
        $this->activityInstanceId = $data['activityInstanceId'];
        $this->taskId = $data['taskId'];
        $this->tenantId = $data['tenantId'];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[propertyId=" . $this->propertyId
                . ", propertyValue=" . $this->propertyValue
                . ", activityInstanceId=" . $this->activityInstanceId
                . ", eventType=" . $this->eventType
                . ", executionId=" . $this->executionId
                . ", id=" . $this->id
                . ", processDefinitionId=" . $this->processDefinitionId
                . ", processInstanceId=" . $this->processInstanceId
                . ", taskId=" . $this->taskId
                . ", tenantId=" . $this->tenantId
                . "]";
    }
}
