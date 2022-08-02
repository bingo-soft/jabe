<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\HistoricVariableInstanceInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    DbEntityLifecycleAwareInterface,
    EnginePersistenceLogger,
    HasDbRevisionInterface,
    HistoricEntityInterface
};
use Jabe\Engine\Impl\History\Event\HistoricVariableUpdateEventEntity;
use Jabe\Engine\Impl\Persistence\Entity\Util\{
    ByteArrayField,
    TypedValueField
};
use Jabe\Engine\Impl\Variable\Serializer\{
    TypedValueSerializerInterface,
    ValueFieldsInterface
};
use Jabe\Engine\Repository\ResourceTypes;
use Jabe\Engine\Variable\Value\TypedValueInterface;
use Jabe\Engine\Impl\Util\ClassNameUtil;

class HistoricVariableInstanceEntity implements ValueFieldsInterface, HistoricVariableInstanceInterface, DbEntityInterface, HasDbRevisionInterface, HistoricEntityInterface, \Serializable, DbEntityLifecycleAwareInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $id;

    protected $processDefinitionKey;
    protected $processDefinitionId;
    protected $rootProcessInstanceId;
    protected $processInstanceId;

    protected $taskId;
    protected $executionId;
    protected $activityInstanceId;
    protected $tenantId;

    /*protected $caseDefinitionKey;
    protected $caseDefinitionId;
    protected $caseInstanceId;
    protected $caseExecutionId;*/

    protected $name;
    protected $revision;
    protected $createTime;

    protected $longValue;
    protected $doubleValue;
    protected $textValue;
    protected $textValue2;

    protected $state = "CREATED";

    protected $removalTime;

    protected $byteArrayField;

    protected $typedValueField;

    public function __construct(HistoricVariableUpdateEventEntity $historyEvent)
    {
        $this->byteArrayField = new ByteArrayField($this, ResourceTypes::history());
        $this->typedValueField = new TypedValueField($this, false);
        $this->updateFromEvent($historyEvent);
    }

    public function updateFromEvent(HistoricVariableUpdateEventEntity $historyEvent): void
    {
        $this->id = $historyEvent->getVariableInstanceId();
        $this->processDefinitionKey = $historyEvent->getProcessDefinitionKey();
        $this->processDefinitionId = $historyEvent->getProcessDefinitionId();
        $this->processInstanceId = $historyEvent->getProcessInstanceId();
        $this->taskId = $historyEvent->getTaskId();
        $this->executionId = $historyEvent->getExecutionId();
        $this->activityInstanceId = $historyEvent->getScopeActivityInstanceId();
        $this->tenantId = $historyEvent->getTenantId();
        /*$this->caseDefinitionKey = $historyEvent->getCaseDefinitionKey();
        $this->caseDefinitionId = $historyEvent->getCaseDefinitionId();
        $this->caseInstanceId = $historyEvent->getCaseInstanceId();
        $this->caseExecutionId = $historyEvent->getCaseExecutionId();*/
        $this->name = $historyEvent->getVariableName();
        $this->longValue = $historyEvent->getLongValue();
        $this->doubleValue = $historyEvent->getDoubleValue();
        $this->textValue = $historyEvent->getTextValue();
        $this->textValue2 = $historyEvent->getTextValue2();
        $this->createTime = $historyEvent->getTimestamp();
        $this->rootProcessInstanceId = $historyEvent->getRootProcessInstanceId();
        $this->removalTime = $historyEvent->getRemovalTime();

        $this->setSerializerName($historyEvent->getSerializerName());

        $this->byteArrayField->deleteByteArrayValue();

        if ($historyEvent->getByteValue() !== null) {
            $this->byteArrayField->setRootProcessInstanceId($this->rootProcessInstanceId);
            $this->byteArrayField->setRemovalTime($this->removalTime);
            $this->setByteArrayValue($historyEvent->getByteValue());
        }
    }

    public function delete(): void
    {
        $this->byteArrayField->deleteByteArrayValue();

        Context::getCommandContext()
          ->getDbEntityManager()
          ->delete($this);
    }

    public function getPersistentState()
    {
        $state = [];
        $state[] = $this->getSerializerName();
        $state[] = $this->textValue;
        $state[] = $this->textValue2;
        $state[] = $this->state;
        $state[] = $this->doubleValue;
        $state[] = $this->longValue;
        $state[] = $this->processDefinitionId;
        $state[] = $this->processDefinitionKey;
        $state[] = $this->getByteArrayId();
        return $state;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getValue()
    {
        return $this->typedValueField->getValue();
    }

    public function getTypedValue(?bool $deserializeValue = null): TypedValueInterface
    {
        return $this->typedValueField->getTypedValue($deserializeValue ?? true, false);
    }

    public function getSerializer(): TypedValueSerializerInterface
    {
        return $this->typedValueField->getSerializer();
    }

    public function getByteArrayValueId(): string
    {
        return $this->byteArrayField->getByteArrayId();
    }

    public function getByteArrayId(): string
    {
        return $this->byteArrayField->getByteArrayId();
    }

    public function setByteArrayId(string $byteArrayId): void
    {
        $this->byteArrayField->setByteArrayId($byteArrayId);
    }

    public function getByteArrayValue(): string
    {
        return $this->byteArrayField->getByteArrayValue();
    }

    // entity lifecycle /////////////////////////////////////////////////////////

    public function postLoad(): void
    {
        // make sure the serializer is initialized
        $this->typedValueField->postLoad();
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getSerializerName(): string
    {
        return $this->typedValueField->getSerializerName();
    }

    public function setSerializerName(string $serializerName): void
    {
        $this->typedValueField->setSerializerName($serializerName);
    }

    public function getTypeName(): string
    {
        return $this->typedValueField->getTypeName();
    }

    public function getVariableTypeName(): string
    {
        return $this->getTypeName();
    }

    public function getVariableName(): string
    {
        return $this->name;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLongValue(): int
    {
        return $this->longValue;
    }

    public function setLongValue(int $longValue): void
    {
        $this->longValue = $longValue;
    }

    public function getDoubleValue(): float
    {
        return $this->doubleValue;
    }

    public function setDoubleValue(float $doubleValue): void
    {
        $this->doubleValue = $doubleValue;
    }

    public function getTextValue(): string
    {
        return $this->textValue;
    }

    public function setTextValue(string $textValue): void
    {
        $this->textValue = $textValue;
    }

    public function getTextValue2(): string
    {
        return $this->textValue2;
    }

    public function setTextValue2(string $textValue2): void
    {
        $this->textValue2 = $textValue2;
    }

    public function setByteArrayValue($bytes): void
    {
        $this->byteArrayField->setByteArrayValue($bytes);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function setProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function setTaskId(string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function setExecutionId(string $executionId): void
    {
        $this->executionId = $executionId;
    }

    public function getActivityInstanceId(): string
    {
        return $this->activityInstanceId;
    }

    public function setActivityInstanceId(string $activityInstanceId): void
    {
        $this->activityInstanceId = $activityInstanceId;
    }

    /*
    public String getCaseDefinitionKey() {
        return caseDefinitionKey;
    }

    public void setCaseDefinitionKey(String caseDefinitionKey) {
      $this->caseDefinitionKey = caseDefinitionKey;
    }

    public String getCaseDefinitionId() {
      return caseDefinitionId;
    }

    public void setCaseDefinitionId(String caseDefinitionId) {
      $this->caseDefinitionId = caseDefinitionId;
    }

    public String getCaseInstanceId() {
      return caseInstanceId;
    }

    public void setCaseInstanceId(String caseInstanceId) {
      $this->caseInstanceId = caseInstanceId;
    }

    public String getCaseExecutionId() {
      return caseExecutionId;
    }

    public void setCaseExecutionId(String caseExecutionId) {
      $this->caseExecutionId = caseExecutionId;
    }*/

    public function getErrorMessage(): string
    {
        return $this->typedValueField->getErrorMessage();
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function setCreateTime(string $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getRemovalTime(): string
    {
        return $this->removalTime;
    }

    public function setRemovalTime(string $removalTime): void
    {
        $this->removalTime = $removalTime;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'processDefinitionKey' => $this->processDefinitionKey,
            'processDefinitionId' => $this->processDefinitionId,
            'rootProcessInstanceId' => $this->rootProcessInstanceId,
            'removalTime' => $this->removalTime,
            'processInstanceId' => $this->processInstanceId,
            'taskId' => $this->taskId,
            'executionId' => $this->executionId,
            'tenantId' => $this->tenantId,
            'name' => $this->name,
            'createTime' => $this->createTime,
            'revision' => $this->revision,
            'longValue' => $this->longValue,
            'doubleValue' => $this->doubleValue,
            'textValue' => $this->textValue,
            'textValue2' => $this->textValue2,
            'state' => $this->state
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->processDefinitionKey = $json->processDefinitionKey;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->rootProcessInstanceId = $json->rootProcessInstanceId;
        $this->removalTime = $json->removalTime;
        $this->processInstanceId = $json->processInstanceId;
        $this->taskId = $json->taskId;
        $this->executionId = $json->executionId;
        $this->tenantId = $json->tenantId;
        $this->name = $json->name;
        $this->createTime = $json->createTime;
        $this->revision = $json->revision;
        $this->longValue = $json->longValue;
        $this->doubleValue = $json->doubleValue;
        $this->textValue = $json->textValue;
        $this->textValue2 = $json->textValue2;
        $this->state = $json->state;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
          . "[id=" . $this->id
          . ", processDefinitionKey=" . $this->processDefinitionKey
          . ", processDefinitionId=" . $this->processDefinitionId
          . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
          . ", removalTime=" . $this->removalTime
          . ", processInstanceId=" . $this->processInstanceId
          . ", taskId=" . $this->taskId
          . ", executionId=" . $this->executionId
          . ", tenantId=" . $this->tenantId
          . ", activityInstanceId=" . $this->activityInstanceId
          /*. ", caseDefinitionKey=" . $this->caseDefinitionKey
          . ", caseDefinitionId=" . $this->caseDefinitionId
          . ", caseInstanceId=" . $this->caseInstanceId
          . ", caseExecutionId=" . $this->caseExecutionId*/
          . ", name=" . $this->name
          . ", createTime=" . $this->createTime
          . ", revision=" . $this->revision
          . ", serializerName=" . $this->getSerializerName()
          . ", longValue=" . $this->longValue
          . ", doubleValue=" . $this->doubleValue
          . ", textValue=" . $this->textValue
          . ", textValue2=" . $this->textValue2
          . ", state=" . $this->state
          . ", byteArrayId=" . $this->getByteArrayId()
          . "]";
    }
}
