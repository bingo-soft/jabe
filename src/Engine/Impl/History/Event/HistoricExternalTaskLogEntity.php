<?php

namespace Jabe\Engine\Impl\History\Event;

use Jabe\Engine\History\{
    ExternalTaskStateImpl,
    HistoricExternalTaskLogInterface
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExternalTaskEntity
};
use Jabe\Engine\Impl\Util\{
    EnsureUtil,
    ExceptionUtil,
    StringUtil
};
use Jabe\Engine\Repository\ResourceTypes;

class HistoricExternalTaskLogEntity extends HistoryEvent implements HistoricExternalTaskLogInterface
{
    private const EXCEPTION_NAME = "historicExternalTaskLog.exceptionByteArray";

    protected $timestamp;

    protected $externalTaskId;

    protected $topicName;
    protected $workerId;
    protected $priority;
    protected $retries;

    protected $errorMessage;

    protected $errorDetailsByteArrayId;
    protected $activityId;

    protected $activityInstanceId;
    protected $tenantId;

    protected $state;

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getExternalTaskId(): string
    {
        return $this->externalTaskId;
    }

    public function setExternalTaskId(string $externalTaskId): void
    {
        $this->externalTaskId = $externalTaskId;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function setTopicName(string $topicName): void
    {
        $this->topicName = $topicName;
    }

    public function getWorkerId(): string
    {
        return $this->workerId;
    }

    public function setWorkerId(string $workerId): void
    {
        $this->workerId = $workerId;
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

    public function setErrorMessage(?string $errorMessage): void
    {
        // note: it is not a clean way to truncate where the history event is produced, since truncation is only
        //   relevant for relational history databases that follow our schema restrictions;
        //   a similar problem exists in ExternalTaskEntity#setErrorMessage where truncation may not be required for custom
        //   persistence implementations
        if ($errorMessage != null && strlen($errorMessage) > ExternalTaskEntity::MAX_EXCEPTION_MESSAGE_LENGTH) {
            $this->errorMessage = substr($errorMessage, 0, ExternalTaskEntity::MAX_EXCEPTION_MESSAGE_LENGTH);
        } else {
            $this->errorMessage = $errorMessage;
        }
    }

    public function getErrorDetailsByteArrayId(): string
    {
        return $this->errorDetailsByteArrayId;
    }

    public function setErrorDetailsByteArrayId(string $errorDetailsByteArrayId): void
    {
        $this->errorDetailsByteArrayId = $errorDetailsByteArrayId;
    }

    public function getErrorDetails(): string
    {
        $byteArray = $this->getErrorByteArray();
        return ExceptionUtil::getExceptionStacktrace($byteArray);
    }

    public function setErrorDetails(string $exception): void
    {
        EnsureUtil::ensureNotNull("exception", "exception", $exception);

        $exceptionBytes = $this->toByteArray($exception);
        $byteArray = $this->createExceptionByteArray(self::EXCEPTION_NAME, $exceptionBytes, ResourceTypes::history());
        $byteArray->setRootProcessInstanceId($rootProcessInstanceId);
        $byteArray->setRemovalTime($removalTime);

        $this->errorDetailsByteArrayId = $byteArray->getId();
    }

    protected function getErrorByteArray(): ?ByteArrayEntity
    {
        if ($this->errorDetailsByteArrayId != null) {
            return Context::getCommandContext()
                ->getDbEntityManager()
                ->selectById(ByteArrayEntity::class, $errorDetailsByteArrayId);
        }
        return null;
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

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function isCreationLog(): bool
    {
        return $this->state == ExternalTaskStateImpl::created()->getStateCode();
    }

    public function isFailureLog(): bool
    {
        return $this->state == ExternalTaskStateImpl::failed()->getStateCode();
    }

    public function isSuccessLog(): bool
    {
        return $this->state == ExternalTaskStateImpl::successful()->getStateCode();
    }

    public function isDeletionLog(): bool
    {
        return $this->state == ExternalTaskStateImpl::deleted()->getStateCode();
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }
}
