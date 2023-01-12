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

    public function serialize()
    {
        return json_encode([
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
        $this->propertyId = $json->propertyId;
        $this->propertyValue = $json->propertyValue;
        $this->activityInstanceId = $json->activityInstanceId;
        $this->taskId = $json->taskId;
        $this->tenantId = $json->tenantId;
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
