<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Application\{
    InvocationContext,
    ProcessApplicationReferenceInterface
};
use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    DbEntityLifecycleAwareInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Engine\Impl\Persistence\Entity\Util\{
    ByteArrayField,
    TypedValueField,
    TypedValueUpdateListenerInterface
};
use Jabe\Engine\Impl\Pvm\Runtime\LegacyBehavior;
use Jabe\Engine\Impl\Variable\Serializer\{
    TypedValueSerializerInterface,
    ValueFieldsInterface
};
use Jabe\Engine\Repository\ResourceTypes;
use Jabe\Engine\Runtime\VariableInstanceInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;
use Jabe\Engine\Impl\Util\ClassNameUtil;

class VariableInstanceEntity implements VariableInstanceInterface, CoreVariableInstanceInterface, ValueFieldsInterface, DbEntityInterface, DbEntityLifecycleAwareInterface, TypedValueUpdateListenerInterface, HasDbRevisionInterface, HasDbReferencesInterface, \Serializable
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $id;
    protected $revision;

    protected $name;

    protected $processDefinitionId;
    protected $processInstanceId;
    protected $executionId;
    protected $taskId;
    protected $batchId;
    /*protected $caseInstanceId;
    protected $caseExecutionId;*/
    protected $activityInstanceId;
    protected $tenantId;

    protected $longValue;
    protected $doubleValue;
    protected $textValue;
    protected $textValue2;
    protected $variableScopeId;

    protected $byteArrayField;

    protected $typedValueField;

    protected $forcedUpdate;

    protected $configuration;

    protected $sequenceCounter = 1;

    /**
     * <p>Determines whether this variable is supposed to be a local variable
     * in case of concurrency in its scope. This affects
     * </p>
     *
     * <ul>
     * <li>tree expansion (not evaluated yet by the engine)
     * <li>activity instance IDs of variable instances: concurrentLocal
     *   variables always receive the activity instance id of their execution
     *   (which may not be the scope execution), while non-concurrentLocal variables
     *   always receive the activity instance id of their scope (which is set in the
     *   parent execution)
     * </ul>
     *
     * <p>
     *   In the future, this field could be used for restoring the variable distribution
     *   when the tree is expanded/compacted multiple times.
     *   On expansion, the goal would be to keep concurrentLocal variables always with
     *   their concurrent replacing executions while non-concurrentLocal variables
     *   stay in the scope execution
     * </p>
     */
    protected $isConcurrentLocal = false;

    /**
     * Determines whether this variable is stored in the data base.
     */
    protected $isTransient = false;

    // transient properties
    protected $execution;

    public function __construct(string $name, TypedValueInterface $value, bool $isTransient)
    {
        $this->byteArrayField = new ByteArrayField($this, ResourceTypes::runtime());
        $this->typedValueField = new TypedValueField($this, true);

        $typedValueField->addImplicitUpdateListener($this);
        $this->name = $name;
        $this->isTransient = $isTransient;
        $typedValueField->setValue($value);
    }

    public static function createAndInsert(string $name, TypedValueInterface $value): VariableInstanceEntity
    {
        $variableInstance = self::create($name, $value, $value->isTransient());
        self::insert($variableInstance);
        return $variableInstance;
    }

    public static function insert(VariableInstanceEntity $variableInstance): void
    {
        if (!$variableInstance->isTransient()) {
            Context::getCommandContext()
            ->getDbEntityManager()
            ->insert($variableInstance);
        }
    }

    public static function create(string $name, TypedValueInterface $value, bool $isTransient): VariableInstanceEntity
    {
        return new VariableInstanceEntity($name, $value, $isTransient);
    }

    public function delete(): void
    {

        if (!$this->isTransient()) {
            $this->typedValueField->notifyImplicitValueUpdate();
        }

        // clear value
        $this->clearValueFields();

        if (!$this->isTransient) {
            // delete variable
            Context::getCommandContext()->getDbEntityManager()->delete($this);
        }
    }

    public function getPersistentState()
    {
        $persistentState = [];
        if ($this->typedValueField->getSerializerName() != null) {
            $persistentState["serializerName"] = $typedValueField->getSerializerName();
        }
        if ($this->longValue != null) {
            $persistentState["longValue"] = $this->longValue;
        }
        if ($this->doubleValue != null) {
            $persistentState["doubleValue"] = $this->doubleValue;
        }
        if ($this->textValue != null) {
            $persistentState["textValue"] = $this->textValue;
        }
        if ($this->textValue2 != null) {
            $persistentState["textValue2"] = $this->textValue2;
        }
        if ($this->byteArrayField->getByteArrayId() != null) {
            $persistentState["byteArrayValueId"] = $this->byteArrayField->getByteArrayId();
        }

        $persistentState["sequenceCounter"] = $this->getSequenceCounter();
        $persistentState["concurrentLocal"] = $this->isConcurrentLocal;
        $persistentState["executionId"] = $this->executionId;
        $persistentState["taskId"] = $this->taskId;
        //$persistentState["caseExecutionId", caseExecutionId);
        //$persistentState["caseInstanceId", caseInstanceId);
        $persistentState["tenantId"] = $this->tenantId;
        $persistentState["processInstanceId"] = $this->processInstanceId;
        $persistentState["processDefinitionId"] = $this->processDefinitionId;

        return $persistentState;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    // lazy initialized relations ///////////////////////////////////////////////

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function setExecutionId(string $executionId): void
    {
        $this->executionId = $executionId;
    }

    /*public void setCaseInstanceId(string $caseInstanceId) {
        $this->caseInstanceId = caseInstanceId;
    }

    public void setCaseExecutionId(string $caseExecutionId) {
        $this->caseExecutionId = caseExecutionId;
    }

    public void setCaseExecution(CaseExecutionEntity caseExecution) {
        if (caseExecution != null) {
            $this->caseInstanceId = caseExecution->getCaseInstanceId();
            $this->caseExecutionId = caseExecution->getId();
            $this->tenantId = caseExecution->getTenantId();
        } else {
            $this->caseInstanceId = null;
            $this->caseExecutionId = null;
            $this->tenantId = null;
        }
    }*/

    // byte array value /////////////////////////////////////////////////////////

    // i couldn't find a easy readable way to extract the common byte array value logic
    // into a common class.  therefor it's duplicated in VariableInstanceEntity,
    // HistoricVariableInstance and HistoricDetailVariableInstanceUpdateEntity

    public function getByteArrayValueId(): string
    {
        return $this->byteArrayField->getByteArrayId();
    }

    public function setByteArrayValueId(string $byteArrayValueId): void
    {
        $this->byteArrayField->setByteArrayId($byteArrayValueId);
    }

    public function getByteArrayValue(): string
    {
        return $this->byteArrayField->getByteArrayValue();
    }

    public function setByteArrayValue(string $bytes): void
    {
        $this->byteArrayField->setByteArrayValue($bytes, $isTransient);
    }

    protected function deleteByteArrayValue(): void
    {
        $this->byteArrayField->deleteByteArrayValue();
    }

    // type /////////////////////////////////////////////////////////////////////

    public function getValue()
    {
        return $this->typedValueField->getValue();
    }

    public function getTypedValue(?bool $deserializeValue = null): TypedValueInterface
    {
        return $this->typedValueField->getTypedValue($deserializeValue ?? true, $this->isTransient);
    }

    public function setValue(TypedValueInterface $value): void
    {
        // clear value fields
        $this->clearValueFields();

        $this->typedValueField->setValue($value);
    }

    public function clearValueFields(): void
    {
        $this->longValue = null;
        $this->doubleValue = null;
        $this->textValue = null;
        $this->textValue2 = null;
        $this->typedValueField->clear();

        if ($byteArrayField->getByteArrayId() != null) {
            $this->deleteByteArrayValue();
            $this->setByteArrayValueId(null);
        }
    }

    public function getTypeName(): string
    {
        return $this->typedValueField->getTypeName();
    }

    // entity lifecycle /////////////////////////////////////////////////////////

    public function postLoad(): void
    {
        // make sure the serializer is initialized
        $this->typedValueField->postLoad();
    }

    // execution ////////////////////////////////////////////////////////////////

    protected function ensureExecutionInitialized(): void
    {
        if ($this->execution == null && $this->executionId != null) {
            $this->execution = Context::getCommandContext()
                ->getExecutionManager()
                ->findExecutionById($this->executionId);
        }
    }

    public function getExecution(): ExecutionEntity
    {
        $this->ensureExecutionInitialized();
        return $this->execution;
    }

    public function setExecution(ExecutionEntity $execution): void
    {
        $this->execution = $execution;
        if ($this->execution == null) {
            $this->executionId = null;
            $this->processInstanceId = null;
            $this->processDefinitionId = null;
            $this->tenantId = null;
        } else {
            $this->setExecutionId($execution->getId());
            $this->processDefinitionId = $execution->getProcessDefinitionId();
            $this->processInstanceId = $execution->getProcessInstanceId();
            $this->tenantId = $execution->getTenantId();
        }
    }

    // case execution ///////////////////////////////////////////////////////////

    /*public CaseExecutionEntity getCaseExecution() {
        if (caseExecutionId != null) {
            return Context
                ->getCommandContext()
                ->getCaseExecutionManager()
                .findCaseExecutionById(caseExecutionId);
        }
        return null;
    }*/

    // getters and setters //////////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTextValue(): string
    {
        return $this->textValue;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    /*public String getCaseInstanceId() {
        return caseInstanceId;
    }

    public String getCaseExecutionId() {
        return caseExecutionId;
    }*/

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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setTextValue(string $textValue): void
    {
        $this->textValue = $textValue;
    }

    public function getName(): string
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

    public function setSerializer(TypedValueSerializerInterface $serializer): void
    {
        $this->typedValueField->setSerializerName($serializer->getName());
    }

    public function setSerializerName(string $type): void
    {
        $this->typedValueField->setSerializerName($type);
    }

    public function getSerializer(): TypedValueSerializerInterface
    {
        return $this->typedValueField->getSerializer();
    }

    public function getTextValue2(): string
    {
        return $this->textValue2;
    }

    public function setTextValue2(string $textValue2): void
    {
        $this->textValue2 = $textValue2;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function setTaskId(string $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function setBatchId(string $batchId): void
    {
        $this->batchId = $batchId;
    }

    public function setTask(TaskEntity $task): void
    {
        if ($task != null) {
            $this->taskId = $task->getId();
            $this->tenantId = $task->getTenantId();

            if ($task->getExecution() != null) {
                $this->setExecution($task->getExecution());
            }
            /*if ($task->getCaseExecution() != null) {
                setCaseExecution(task->getCaseExecution());
            }*/
        } else {
            $this->taskId = null;
            $this->tenantId = null;
            $this->setExecution(null);
            //setCaseExecution(null);
        }
    }

    public function getActivityInstanceId(): string
    {
        return $this->activityInstanceId;
    }

    public function setActivityInstanceId(string $activityInstanceId): void
    {
        $this->activityInstanceId = $activityInstanceId;
    }

    public function getSerializerName(): string
    {
        return $this->typedValueField->getSerializerName();
    }

    public function getErrorMessage(): string
    {
        return $this->typedValueField->getErrorMessage();
    }

    public function getVariableScopeId(): ?string
    {
        if ($this->variableScopeId != null) {
            return $this->variableScopeId;
        }

        if ($this->taskId != null) {
            return $this->taskId;
        }

        if ($this->executionId != null) {
            return $this->executionId;
        }

        //return caseExecutionId;
    }

    public function setVariableScopeId(string $variableScopeId): void
    {
        $this->variableScopeId = $variableScopeId;
    }

    protected function getVariableScope(): ?VariableScopeInterface
    {
        if ($this->taskId != null) {
            return $this->getTask();
        } elseif ($this->executionId != null) {
            return $this->getExecution();
        } else {
            return null;
        }
        /*elseif (caseExecutionId != null) {
            return getCaseExecution();
        }*/
    }

    protected function getTask(): ?TaskEntity
    {
        if ($this->taskId != null) {
            return Context::getCommandContext()->getTaskManager()->findTaskById($this->taskId);
        } else {
            return null;
        }
    }

    //sequence counter ///////////////////////////////////////////////////////////

    public function getSequenceCounter(): int
    {
        return $this->sequenceCounter;
    }

    public function setSequenceCounter(int $sequenceCounter): void
    {
        $this->sequenceCounter = $sequenceCounter;
    }

    public function incrementSequenceCounter(): void
    {
        $this->sequenceCounter += 1;
    }


    public function isConcurrentLocal(): bool
    {
        return $this->isConcurrentLocal;
    }

    public function setConcurrentLocal(bool $isConcurrentLocal): void
    {
        $this->isConcurrentLocal = $isConcurrentLocal;
    }

    public function onImplicitValueUpdate(TypedValueInterface $updatedValue): void
    {
        // note: this implementation relies on the
        //   behavior that the variable scope
        //   of variable value can never become null

        $targetProcessApplication = getContextProcessApplication();
        if ($targetProcessApplication != null) {
            $scope = $this;
            Context::executeWithinProcessApplication(
                function () use ($scope, $updatedValue) {
                    $scope->getVariableScope()->setVariableLocal($scope->name, $updatedValue);
                    return null;
                },
                $targetProcessApplication,
                new InvocationContext($this->getExecution())
            );
        } else {
            if (!$this->isTransient) {
                $this->getVariableScope()->setVariableLocal($this->name, $updatedValue);
            }
        }
    }

    protected function getContextProcessApplication(): ?ProcessApplicationReferenceInterface
    {
        if ($this->taskId != null) {
            return ProcessApplicationContextUtil::getTargetProcessApplication($this->getTask());
        } elseif ($this->executionId != null) {
            return ProcessApplicationContextUtil::getTargetProcessApplication($this->getExecution());
        } else {
            return null;
        }
        /*elseif (caseExecutionId != null) {
            return ProcessApplicationContextUtil->getTargetProcessApplication(getCaseExecution());
        } */
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'revision' => $this->revision,
            'name' => $this->name,
            'processDefinitionId' => $this->processDefinitionId,
            'processInstanceId' => $this->processInstanceId,
            'executionId' => $this->executionId,
            'taskId' => $this->taskId,
            'activityInstanceId' => $this->activityInstanceId,
            'tenantId' => $this->tenantId,
            'longValue' => $this->longValue,
            'doubleValue' => $this->doubleValue,
            'textValue' => $this->textValue,
            'textValue2' => $this->textValue2,
            'byteArrayValueId' => $this->byteArrayValueId,
            'configuration' => $this->configuration,
            'isConcurrentLocal' => $this->isConcurrentLocal
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->revision = $json->revision;
        $this->name = $json->name;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->processInstanceId = $json->processInstanceId;
        $this->executionId = $json->executionId;
        $this->taskId = $json->taskId;
        $this->activityInstanceId = $json->activityInstanceId;
        $this->tenantId = $json->tenantId;
        $this->longValue = $json->longValue;
        $this->doubleValue = $json->doubleValue;
        $this->textValue = $json->textValue;
        $this->textValue2 = $json->textValue2;
        $this->byteArrayValueId = $json->byteArrayValueId;
        $this->configuration = $json->configuration;
        $this->isConcurrentLocal = $json->isConcurrentLocal;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
        . "[id=" . $this->id
        . ", revision=" . $this->revision
        . ", name=" . $this->name
        . ", processDefinitionId=" . $this->processDefinitionId
        . ", processInstanceId=" . $this->processInstanceId
        . ", executionId=" . $this->executionId
        //. ", caseInstanceId=" . caseInstanceId
        //. ", caseExecutionId=" . caseExecutionId
        . ", taskId=" . $this->taskId
        . ", activityInstanceId=" . $this->activityInstanceId
        . ", tenantId=" . $this->tenantId
        . ", longValue=" . $this->longValue
        . ", doubleValue=" . $this->doubleValue
        . ", textValue=" . $this->textValue
        . ", textValue2=" . $this->textValue2
        . ", byteArrayValueId=" . $this->getByteArrayValueId()
        . ", configuration=" . $this->configuration
        . ", isConcurrentLocal=" . $this->isConcurrentLocal
        . "]";
    }

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj == null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->id == null) {
            if ($obj->id != null) {
                return false;
            }
        } elseif ($this->id != $obj->id) {
            return false;
        }
        return true;
    }

    /**
     * @param isTransient
     *          <code>true</code>, if the variable is not stored in the data base.
     *          Default is <code>false</code>.
     */
    public function setTransient(bool $isTransient): void
    {
        $this->isTransient = $isTransient;
    }

    /**
     * @return <code>true</code>, if the variable is transient. A transient
     *         variable is not stored in the data base.
     */
    public function isTransient(): bool
    {
        return $this->isTransient;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];

        if ($this->processInstanceId != null) {
            $referenceIdAndClass[$this->processInstanceId] = ExecutionEntity::class;
        }
        if ($this->executionId != null) {
            $referenceIdAndClass[$this->executionId] = ExecutionEntity::class;
        }
        /*if (caseInstanceId != null){
            referenceIdAndClass.put(caseInstanceId, CaseExecutionEntity::class);
        }
        if (caseExecutionId != null){
            referenceIdAndClass.put(caseExecutionId, CaseExecutionEntity::class);
        }*/
        if ($this->getByteArrayValueId() != null) {
            $referenceIdAndClass[$this->getByteArrayValueId()] = ByteArrayEntity::class;
        }

        return $referenceIdAndClass;
    }
}
