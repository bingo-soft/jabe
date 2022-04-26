<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\EntityTypes;
use Jabe\Engine\Delegate\BpmnError;
use Jabe\Engine\ExternalTask\ExternalTaskInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Bpmn\Helper\{
    BpmnExceptionHandler,
    BpmnProperties
};
use Jabe\Engine\Impl\Bpmn\Parser\ErrorEventDefinition;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Engine\Impl\Incident\{
    IncidentContext,
    IncidentHandling
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Engine\Impl\Util\{
    ClockUtil,
    EnsureUtil,
    ExceptionUtil,
    StringUtil
};
use Jabe\Engine\Repository\ResourceTypes;
use Jabe\Engine\Runtime\IncidentInterface;

class ExternalTaskEntity implements ExternalTaskInterface, DbEntityInterface, HasDbRevisionInterface, HasDbReferencesInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    private const EXCEPTION_NAME = "externalTask.exceptionByteArray";

    /**
     * Note: {@link String#length()} counts Unicode supplementary
     * characters twice, so for a String consisting only of those,
     * the limit is effectively MAX_EXCEPTION_MESSAGE_LENGTH / 2
     */
    public const MAX_EXCEPTION_MESSAGE_LENGTH = 666;

    protected $id;
    protected $revision;

    protected $topicName;
    protected $workerId;
    protected $lockExpirationTime;
    protected $retries;
    protected $errorMessage;

    protected $errorDetailsByteArray;
    protected $errorDetailsByteArrayId;

    protected $suspensionState;
    protected $executionId;
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $processDefinitionVersionTag;
    protected $activityId;
    protected $activityInstanceId;
    protected $tenantId;
    protected $priority;

    protected $extensionProperties = [];

    protected $execution;

    protected $businessKey;

    protected $lastFailureLogId;

    public function __construct()
    {
        $this->suspensionState = SuspensionState::active()->getStateCode();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function setTopicName(string $topic): void
    {
        $this->topicName = $topic;
    }

    public function getWorkerId(): string
    {
        return $this->workerId;
    }

    public function setWorkerId(string $workerId): void
    {
        $this->workerId = $workerId;
    }

    public function getLockExpirationTime(): string
    {
        return $this->lockExpirationTime;
    }

    public function setLockExpirationTime(string $lockExpirationTime): void
    {
        $this->lockExpirationTime = $lockExpirationTime;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function setExecutionId(string $executionId): void
    {
        $this->executionId = $executionId;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function setProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->processDefinitionKey = $processDefinitionKey;
    }

    public function getProcessDefinitionVersionTag(): string
    {
        return $this->processDefinitionVersionTag;
    }

    public function setProcessDefinitionVersionTag(string $processDefinitionVersionTag): void
    {
        $this->processDefinitionVersionTag = $processDefinitionVersionTag;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getActivityInstanceId(): string
    {
        return $this->activityInstanceId;
    }

    public function setActivityInstanceId(string $activityInstanceId): void
    {
        $this->activityInstanceId = $activityInstanceId;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getSuspensionState(): int
    {
        return $this->suspensionState;
    }

    public function setSuspensionState(int $suspensionState): void
    {
        $this->suspensionState = $suspensionState;
    }

    public function isSuspended(): bool
    {
        return $suspensionState == SuspensionState::suspended()->getStateCode();
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function setRetries(int $retries): void
    {
        $this->retries = $retries;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function areRetriesLeft(): bool
    {
        return $this->retries == null || $this->retries > 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function setBusinessKey(string $businessKey): void
    {
        $this->businessKey = $businessKey;
    }

    public function getExtensionProperties(): array
    {
        return $this->extensionProperties;
    }

    public function setExtensionProperties(array $extensionProperties): void
    {
        $this->extensionProperties = $extensionProperties;
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["topic"] = $this->topicName;
        $persistentState["workerId"] = $this->workerId;
        $persistentState["lockExpirationTime"] = $this->lockExpirationTime;
        $persistentState["retries"] = $this->retries;
        $persistentState["errorMessage"] = $this->errorMessage;
        $persistentState["executionId"] = $this->executionId;
        $persistentState["processInstanceId"] = $this->processInstanceId;
        $persistentState["processDefinitionId"] = $this->processDefinitionId;
        $persistentState["processDefinitionKey"] = $this->processDefinitionKey;
        $persistentState["processDefinitionVersionTag"] = $this->processDefinitionVersionTag;
        $persistentState["activityId"] = $this->activityId;
        $persistentState["activityInstanceId"] = $this->activityInstanceId;
        $persistentState["suspensionState"] = $this->suspensionState;
        $persistentState["tenantId"] = $this->tenantId;
        $persistentState["priority"] = $this->priority;

        if ($errorDetailsByteArrayId != null) {
            $persistentState["errorDetailsByteArrayId"] = $errorDetailsByteArrayId;
        }

        return $persistentState;
    }

    public function insert(): void
    {
        Context::getCommandContext()
        ->getExternalTaskManager()
        ->insert($this);

        $this->getExecution()->addExternalTask($this);
    }

    /**
     * Method implementation relies on the command context object,
     * therefore should be invoked from the commands only
     *
     * @return error details persisted in byte array table
     */
    public function getErrorDetails(): string
    {
        $byteArray = $this->getErrorByteArray();
        return ExceptionUtil::getExceptionStacktrace($byteArray);
    }

    public function setErrorMessage(string $errorMessage): void
    {
        if ($errorMessage != null && strlen($errorMessage) > self::MAX_EXCEPTION_MESSAGE_LENGTH) {
            $this->errorMessage = substr($errorMessage, 0, self::MAX_EXCEPTION_MESSAGE_LENGTH);
        } else {
            $this->errorMessage = $errorMessage;
        }
    }

    protected function setErrorDetails(string $exception): void
    {
        EnsureUtil::ensureNotNull("exception", "exception", $exception);

        $exceptionBytes = StringUtil::toByteArray($exception);

        $byteArray = $this->getErrorByteArray();

        if ($byteArray == null) {
            $byteArray = ExceptionUtil::createExceptionByteArray(self::EXCEPTION_NAME, $exceptionBytes, ResourceTypes::runtime());
            $this->errorDetailsByteArrayId = $byteArray->getId();
            $this->errorDetailsByteArray = $byteArray;
        } else {
            $byteArray->setBytes($exceptionBytes);
        }
    }

    public function getErrorDetailsByteArrayId(): string
    {
        return $this->errorDetailsByteArrayId;
    }

    protected function getErrorByteArray(): ByteArrayEntity
    {
        $this->ensureErrorByteArrayInitialized();
        return $this->errorDetailsByteArray;
    }

    protected function ensureErrorByteArrayInitialized(): void
    {
        if (empty($this->errorDetailsByteArray) && $this->errorDetailsByteArrayId != null) {
            $this->errorDetailsByteArray = Context::getCommandContext()
                ->getDbEntityManager()
                ->selectById(ByteArrayEntity::class, $this->errorDetailsByteArrayId);
        }
    }

    public function delete(): void
    {
        $this->deleteFromExecutionAndRuntimeTable(false);
        $this->produceHistoricExternalTaskDeletedEvent();
    }

    protected function deleteFromExecutionAndRuntimeTable(bool $incidentResolved): void
    {
        $this->getExecution()->removeExternalTask($this);

        $commandContext = Context::getCommandContext();

        $activityInstanceIdcommandContext
        ->getExternalTaskManager()
        ->delete($this);

        // Also delete the external tasks's error details byte array
        if ($errorDetailsByteArrayId != null) {
            $commandContext->getByteArrayManager()->deleteByteArrayById($errorDetailsByteArrayId);
        }

        $this->removeIncidents($incidentResolved);
    }

    protected function removeIncidents(bool $incidentResolved): void
    {
        $incidentContext = $this->createIncidentContext();
        IncidentHandling::removeIncidents(IncidentInterface::EXTERNAL_TASK_HANDLER_TYPE, $incidentContext, $incidentResolved);
    }

    public function complete(array $variables, array $localVariables): void
    {
        $this->ensureActive();

        $associatedExecution = $this->getExecution();

        $this->ensureVariablesSet($associatedExecution, $variables, $localVariables);

        if ($this->evaluateThrowBpmnError($associatedExecution, false)) {
            return;
        }

        $this->deleteFromExecutionAndRuntimeTable(true);

        $this->produceHistoricExternalTaskSuccessfulEvent();

        $associatedExecution->signal(null, null);
    }

    /**
     * process failed state, make sure that binary entity is created for the errorMessage, shortError
     * message does not exceed limit, handle properly retry counts and incidents
     *
     * @param errorMessage - short error message text
     * @param errorDetails - full error details
     * @param retries - updated value of retries left
     * @param retryDuration - used for lockExpirationTime calculation
     */
    public function failed(string $errorMessage, string $errorDetails, int $retries, int $retryDuration, array $variables, array $localVariables): void
    {
        $this->ensureActive();

        $associatedExecution = $this->getExecution();

        $this->ensureVariablesSet($this->execution, $variables, $localVariables);

        $this->setErrorMessage($errorMessage);

        if (!empty($errorDetails)) {
            $this->setErrorDetails($errorDetails);
        }

        if ($this->evaluateThrowBpmnError($associatedExecution, true)) {
            return;
        }
        $dt = new \DateTime();
        $dt->setTimestamp(ClockUtil::getCurrentTime()->getTimestamp() + $retryDuration);
        $this->lockExpirationTime = $dt->format('c');
        $this->produceHistoricExternalTaskFailedEvent();
        $this->setRetriesAndManageIncidents($retries);
    }

    public function bpmnError(string $errorCode, string $errorMessage, array $variables): void
    {
        $this->ensureActive();
        $activityExecution = $this->getExecution();
        $bpmnError = null;
        if ($errorMessage != null) {
            $bpmnError = new BpmnError($errorCode, $errorMessage);
        } else {
            $bpmnError = new BpmnError($errorCode);
        }
        try {
            if (!empty($variables)) {
                $activityExecution->setVariables($variables);
            }
            BpmnExceptionHandler::propagateBpmnError($bpmnError, $activityExecution);
        } catch (\Exception $ex) {
            //throw ProcessEngineLogger.CMD_LOGGER.exceptionBpmnErrorPropagationFailed(errorCode, ex);
            throw $ex;
        }
    }

    public function setRetriesAndManageIncidents(int $retries): void
    {

        if ($this->areRetriesLeft() && $this->retries <= 0) {
            $this->createIncident();
        } elseif (!$this->areRetriesLeft() && $this->retries > 0) {
            $this->removeIncidents(true);
        }

        $this->setRetries($this->retries);
    }

    protected function createIncident(): void
    {
        $incidentContext = $this->createIncidentContext();
        $incidentContext->setHistoryConfiguration($this->getLastFailureLogId());

        IncidentHandling::createIncident(IncidentInterface::EXTERNAL_TASK_HANDLER_TYPE, $incidentContext, $this->errorMessage);
    }

    protected function createIncidentContext(): IncidentContext
    {
        $context = new IncidentContext();
        $context->setProcessDefinitionId($this->processDefinitionId);
        $context->setExecutionId($this->executionId);
        $context->setActivityId($this->activityId);
        $context->setTenantId($this->tenantId);
        $context->setConfiguration($this->id);
        return $context;
    }

    public function lock(string $workerId, int $lockDuration): void
    {
        $this->workerId = $workerId;
        $dt = new \DateTime();
        $dt->setTimestamp(ClockUtil::getCurrentTime()->getTimestamp() + $lockDuration);
        $this->lockExpirationTime = $dt->format('c');
    }

    public function getExecution(?bool $validateExistence = true): ExecutionEntity
    {
        $this->ensureExecutionInitialized($validateExistence);
        return $this->execution;
    }

    public function setExecution(ExecutionEntity $execution): void
    {
        $this->execution = $execution;
    }

    protected function ensureExecutionInitialized(bool $validateExistence): void
    {
        if ($this->execution == null) {
            $execution = Context::getCommandContext()->getExecutionManager()->findExecutionById($this->executionId);

            if ($validateExistence) {
                EnsureUtil::ensureNotNull(
                    "Cannot find execution with id " . $this->executionId . " for external task " . $this->id,
                    "execution",
                    $execution
                );
            }
        }
    }

    protected function ensureActive(): void
    {
        if ($this->suspensionState == SuspensionState::suspended()->getStateCode()) {
            //throw LOG.suspendedEntityException(EntityTypes.EXTERNAL_TASK, id);
            throw new \Exception("Execution");
        }
    }

    protected function ensureVariablesSet(ExecutionEntity $execution, array $variables, array $localVariables): void
    {
        if (!empty($variables)) {
            $execution->setVariables($variables);
        }

        if (!empty($localVariables)) {
            $execution->setVariablesLocal($localVariables);
        }
    }

    protected function evaluateThrowBpmnError(ExecutionEntity $execution, bool $continueOnException): bool
    {
        $errorEventDefinitions = $execution->getActivity()->getProperty(BpmnProperties::errorEventDefinition()->getName());
        if (!empty($errorEventDefinitions)) {
            foreach ($errorEventDefinitions as $errorEventDefinition) {
                if ($this->errorEventDefinitionMatches($errorEventDefinition, $continueOnException)) {
                    $this->bpmnError($errorEventDefinition->getErrorCode(), $errorMessage, null);
                    return true;
                }
            }
        }
        return false;
    }

    protected function errorEventDefinitionMatches(ErrorEventDefinition $errorEventDefinition, bool $continueOnException): bool
    {
        try {
            return $errorEventDefinition->getExpression() != null && $errorEventDefinition->getExpression()->getValue($this->getExecution()) == true;
        } catch (\Exception $exception) {
            if ($continueOnException) {
                //ProcessEngineLogger.EXTERNAL_TASK_LOGGER.errorEventDefinitionEvaluationException(id, camundaErrorEventDefinition, exception);
                return false;
            }
            throw $exception;
        }
    }

    public function __toString()
    {
        return "ExternalTaskEntity ["
            . "id=" . $this->id
            . ", revision=" . $this->revision
            . ", topicName=" . $this->topicName
            . ", workerId=" . $this->workerId
            . ", lockExpirationTime=" . $this->lockExpirationTime
            . ", priority=" . $this->priority
            . ", errorMessage=" . $this->errorMessage
            . ", errorDetailsByteArray=" . $this->errorDetailsByteArray
            . ", errorDetailsByteArrayId=" . $this->errorDetailsByteArrayId
            . ", executionId=" . $this->executionId . "]";
    }

    public function unlock(): void
    {
        $this->workerId = null;
        $this->lockExpirationTime = null;

        Context::getCommandContext()
        ->getExternalTaskManager()
        ->fireExternalTaskAvailableEvent();
    }

    public static function createAndInsert(ExecutionEntity $execution, string $topic, int $priority): ExternalTaskEntity
    {
        $externalTask = new ExternalTaskEntity();

        $externalTask->setTopicName($topic);
        $externalTask->setExecutionId($execution->getId());
        $externalTask->setProcessInstanceId($execution->getProcessInstanceId());
        $externalTask->setProcessDefinitionId($execution->getProcessDefinitionId());
        $externalTask->setActivityId($execution->getActivityId());
        $externalTask->setActivityInstanceId($execution->getActivityInstanceId());
        $externalTask->setTenantId($execution->getTenantId());
        $externalTask->setPriority($priority);

        $processDefinition = $execution->getProcessDefinition();
        $externalTask->setProcessDefinitionKey($processDefinition->getKey());

        $externalTask->insert();
        $externalTask->produceHistoricExternalTaskCreatedEvent();

        return $externalTask;
    }

    protected function produceHistoricExternalTaskCreatedEvent(): void
    {
        $commandContext = Context::getCommandContext();
        $commandContext->getHistoricExternalTaskLogManager()->fireExternalTaskCreatedEvent($this);
    }

    protected function produceHistoricExternalTaskFailedEvent(): void
    {
        $commandContext = Context::getCommandContext();
        $commandContext->getHistoricExternalTaskLogManager()->fireExternalTaskFailedEvent($this);
    }

    protected function produceHistoricExternalTaskSuccessfulEvent(): void
    {
        $commandContext = Context::getCommandContext();
        $commandContext->getHistoricExternalTaskLogManager()->fireExternalTaskSuccessfulEvent($this);
    }

    protected function produceHistoricExternalTaskDeletedEvent(): void
    {
        $commandContext = Context::getCommandContext();
        $commandContext->getHistoricExternalTaskLogManager()->fireExternalTaskDeletedEvent($this);
    }

    public function extendLock(int $newLockExpirationTime): void
    {
        $this->ensureActive();
        $dt = new \DateTime();
        $dt->setTimestamp(ClockUtil::getCurrentTime()->getTimestamp() + $newLockExpirationTime);
        $newTime = $dt->format('c');
        $this->lockExpirationTime = $newTime;
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];

        if ($this->executionId != null) {
            $referenceIdAndClass[$executionId] = ExecutionEntity::class;
        }
        if ($this->errorDetailsByteArrayId != null) {
            $referenceIdAndClass[$this->errorDetailsByteArrayId] = ByteArrayEntity::class;
        }

        return $referenceIdAndClass;
    }

    public function getLastFailureLogId(): ?string
    {
        return $this->lastFailureLogId;
    }

    public function setLastFailureLogId(string $lastFailureLogId): void
    {
        $this->lastFailureLogId = $lastFailureLogId;
    }
}
