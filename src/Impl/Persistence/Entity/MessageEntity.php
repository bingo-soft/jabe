<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Util\ClassNameUtil;

class MessageEntity extends JobEntity
{
    public const TYPE = "message";

    private $repeat = null;

    public function getRepeat(): ?string
    {
        return $this->repeat;
    }

    public function setRepeat(?string $repeat): void
    {
        $this->repeat = $repeat;
    }

    public function getType(): ?string
    {
        return self::TYPE;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'revision' => $this->revision,
            'duedate' => $this->duedate,
            'lockOwner' => $this->lockOwner,
            'lockExpirationTime' => $this->lockExpirationTime,
            'executionId' => $this->executionId,
            'processInstanceId' => $this->processInstanceId,
            'isExclusive' => $this->isExclusive,
            'jobDefinitionId' => $this->jobDefinitionId,
            'jobHandlerType' => $this->jobHandlerType,
            'jobHandlerConfiguration' => $this->jobHandlerConfiguration,
            'exceptionByteArray' => serialize($this->exceptionByteArray),
            'exceptionByteArrayId' => $this->exceptionByteArrayId,
            'exceptionMessage' => $this->exceptionMessage,
            'failedActivityId' => $this->failedActivityId,
            'deploymentId' => $this->deploymentId,
            'priority' => $this->priority,
            'tenantId' => $this->tenantId,
            'repeat' => $this->repeat
        ];
    }

    public function __unserialize(array $data): void
    {
        parent::unserialize($data);
        $this->repeat = $data['repeat'];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[repeat=" . $this->repeat
                . ", id=" . $this->id
                . ", revision=" . $this->revision
                . ", duedate=" . $this->duedate
                . ", lockOwner=" . $this->lockOwner
                . ", lockExpirationTime=" . $this->lockExpirationTime
                . ", executionId=" . $this->executionId
                . ", processInstanceId=" . $this->processInstanceId
                . ", isExclusive=" . $this->isExclusive
                . ", retries=" . $this->retries
                . ", jobHandlerType=" . $this->jobHandlerType
                . ", jobHandlerConfiguration=" . $this->jobHandlerConfiguration
                . ", exceptionByteArray=" . $this->exceptionByteArray
                . ", exceptionByteArrayId=" . (isset($this->exceptionByteArrayId) ? $this->exceptionByteArrayId : null)
                . ", exceptionMessage=" . $this->exceptionMessage
                . ", deploymentId=" . $this->deploymentId
                . "]";
    }
}
