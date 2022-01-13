<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\HistoricVariableUpdateInterface;
use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Db\{
    DbEntityLifecycleAwareInterface,
    EnginePersistenceLogger
};
use BpmPlatform\Engine\Impl\Db\EntityManager\DbEntityManager;
use BpmPlatform\Engine\Impl\History\Event\HistoricVariableUpdateEventEntity;
use BpmPlatform\Engine\Impl\Persistence\Entity\Util\{
    ByteArrayField,
    TypedValueField
};
use BpmPlatform\Engine\Impl\Variable\Serializer\{
    TypedValueSerializerInterface,
    ValueFieldsInterface
};
use BpmPlatform\Engine\Repository\ResourceTypes;
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;
use BpmPlatform\Engine\Impl\Util\ClassNameUtil;

class HistoricDetailVariableInstanceUpdateEntity extends HistoricVariableUpdateEventEntity implements ValueFieldsInterface, HistoricVariableUpdateInterface, DbEntityLifecycleAwareInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $typedValueField;// = new TypedValueField(this, false);

    protected $byteArrayField; //= new ByteArrayField(this, ResourceTypes.HISTORY);

    public function __construct()
    {
        $this->typedValueField = new TypedValueField($this, false);
        $this->byteArrayField = new ByteArrayField($this, ResourceTypes::history());
    }

    public function getValue()
    {
        return $this->typedValueField->getValue();
    }

    public function getTypedValue(?bool $deserializeValue = false): TypedValueInterface
    {
        return $typedValueField->getTypedValue($deserializeValue, false);
    }

    public function delete(): void
    {
        $dbEntityManger = Context::getCommandContext()
            ->getDbEntityManager();

        $dbEntityManger->delete($this);

        $byteArrayField->deleteByteArrayValue();
    }

    public function getSerializer(): TypedValueSerializerInterface
    {
        return $this->typedValueField->getSerializer();
    }

    public function getErrorMessage(): string
    {
        return $this->typedValueField->getErrorMessage();
    }

    public function setByteArrayId(string $id): void
    {
        $this->byteArrayField->setByteArrayId($id);
    }

    public function getSerializerName(): string
    {
        return $this->typedValueField->getSerializerName();
    }

    public function setSerializerName(string $serializerName): void
    {
        $this->typedValueField->setSerializerName($serializerName);
    }

    public function getByteArrayValueId(): string
    {
        return $this->byteArrayField->getByteArrayId();
    }

    public function getByteArrayValue(): string
    {
        return $this->byteArrayField->getByteArrayValue();
    }

    public function setByteArrayValue(string $bytes): void
    {
        $this->byteArrayField->setByteArrayValue($bytes);
    }

    public function getName(): string
    {
        return $this->getVariableName();
    }

    // entity lifecycle /////////////////////////////////////////////////////////

    public function postLoad(): void
    {
        // make sure the serializer is initialized
        $this->typedValueField->postLoad();
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getTypeName(): string
    {
        return $this->typedValueField->getTypeName();
    }

    public function getVariableTypeName(): string
    {
        return $this->getTypeName();
    }

    public function getTime(): string
    {
        return $this->timestamp;
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
                . ", eventType=" . $this->eventType
                . ", executionId=" . $this->executionId
                . ", id=" . $this->id
                . ", processDefinitionId=" . $this->processDefinitionId
                . ", processInstanceId=" . $this->processInstanceId
                . ", taskId=" . $this->taskId
                . ", timestamp=" . $this->timestamp
                . "]";
    }
}