<?php

namespace Jabe\Impl\History\Event;

use Jabe\Impl\Util\ClassNameUtil;

class HistoricVariableUpdateEventEntity extends HistoricDetailEventEntity
{
    protected int $revision = 0;

    protected $variableName;
    protected $variableInstanceId;
    protected $scopeActivityInstanceId;

    protected $serializerName;

    protected $longValue;
    protected $doubleValue;
    protected $textValue;
    protected $textValue2;
    protected $byteValue;

    protected $byteArrayId;

    protected bool $isInitial = false;

    // getter / setters ////////////////////////////

    public function getSerializerName(): ?string
    {
        return $this->serializerName;
    }

    public function setSerializerName(?string $serializerName): void
    {
        $this->serializerName = $serializerName;
    }

    public function getVariableName(): ?string
    {
        return $this->variableName;
    }

    public function setVariableName(?string $variableName): void
    {
        $this->variableName = $variableName;
    }

    public function getLongValue(): ?int
    {
        return $this->longValue;
    }

    public function setLongValue(?int $longValue): void
    {
        $this->longValue = $longValue;
    }

    public function getDoubleValue(): ?float
    {
        return $this->doubleValue;
    }

    public function setDoubleValue(?float $doubleValue): void
    {
        $this->doubleValue = $doubleValue;
    }

    public function getTextValue(): ?string
    {
        return $this->textValue;
    }

    public function setTextValue(?string $textValue): void
    {
        $this->textValue = $textValue;
    }

    public function getTextValue2(): ?string
    {
        return $this->textValue2;
    }

    public function setTextValue2(?string $textValue2): void
    {
        $this->textValue2 = $textValue2;
    }

    public function getByteValue(): ?string
    {
        return $this->byteValue;
    }

    public function setByteValue(?string $byteValue): void
    {
        $this->byteValue = $byteValue;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function setByteArrayId(?string $id): void
    {
        $this->byteArrayId = $id;
    }

    public function getByteArrayId(): ?string
    {
        return $this->byteArrayId;
    }

    public function getVariableInstanceId(): ?string
    {
        return $this->variableInstanceId;
    }

    public function setVariableInstanceId(?string $variableInstanceId): void
    {
        $this->variableInstanceId = $variableInstanceId;
    }

    public function getScopeActivityInstanceId(): ?string
    {
        return $this->scopeActivityInstanceId;
    }

    public function setScopeActivityInstanceId(?string $scopeActivityInstanceId): void
    {
        $this->scopeActivityInstanceId = $scopeActivityInstanceId;
    }

    public function setInitial(bool $isInitial): void
    {
        $this->isInitial = $isInitial;
    }

    public function isInitial(): bool
    {
        return $this->isInitial;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'eventType' => $this->eventType,
            'executionId' => $this->executionId,
            'processDefinitionId' => $this->processDefinitionId,
            'processInstanceId' => $this->processInstanceId,
            'activityInstanceId' => $this->activityInstanceId,
            'taskId' => $this->taskId,
            'timestamp' => $this->timestamp,
            'tenantId' => $this->tenantId,
            'variableName' => $this->variableName,
            'variableInstanceId' => $this->variableInstanceId,
            'revision' => $this->revision,
            'serializerName' => $this->serializerName,
            'longValue' => $this->longValue,
            'textValue' => $this->textValue,
            'textValue2' => $this->textValue2,
            'byteArrayId' => $this->byteArrayId,
            'scopeActivityInstanceId' => $this->scopeActivityInstanceId,
            'isInitial' => $this->isInitial
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
        $this->activityInstanceId = $json->activityInstanceId;
        $this->taskId = $json->taskId;
        $this->timestamp = $json->timestamp;
        $this->tenantId = $json->tenantId;
        $this->variableName = $json->variableName;
        $this->variableInstanceId = $json->variableInstanceId;
        $this->revision = $json->revision;
        $this->serializerName = $json->serializerName;
        $this->longValue = $json->longValue;
        $this->textValue = $json->textValue;
        $this->textValue2 = $json->textValue2;
        $this->byteArrayId = $json->byteArrayId;
        $this->scopeActivityInstanceId = $json->scopeActivityInstanceId;
        $this->isInitial = $json->isInitial;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
             . "[variableName=" . $this->variableName
             . ", variableInstanceId=" . $this->variableInstanceId
             . ", revision=" . $this->revision
             . ", serializerName=" . $this->serializerName
             . ", longValue=" . $this->longValue
             . ", doubleValue=" . $this->doubleValue
             . ", textValue=" . $this->textValue
             . ", textValue2=" . $this->textValue2
             . ", byteArrayId=" . $this->byteArrayId
             . ", activityInstanceId=" . $this->activityInstanceId
             . ", scopeActivityInstanceId=" . $this->scopeActivityInstanceId
             . ", eventType=" . $this->eventType
             . ", executionId=" . $this->executionId
             . ", id=" . $this->id
             . ", processDefinitionId=" . $this->processInstanceId
             . ", processInstanceId=" . $this->processInstanceId
             . ", taskId=" . $this->taskId
             . ", timestamp=" . $this->timestamp
             . ", tenantId=" . $this->tenantId
             . ", isInitial=" . $this->isInitial
             . "]";
    }
}
