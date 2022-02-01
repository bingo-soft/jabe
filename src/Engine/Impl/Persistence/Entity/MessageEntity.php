<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\JobExecutor\MessageJobDeclaration;
use BpmPlatform\Engine\Impl\Util\ClassNameUtil;

class MessageEntity extends JobEntity
{
    public const TYPE = "message";

    private $repeat = null;

    public function getRepeat(): ?string
    {
        return $this->repeat;
    }

    public function setRepeat(string $repeat): void
    {
        $this->repeat = $repeat;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function serialize()
    {
        return json_encode([
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
        ]);
    }

    public function unserialize($data)
    {
        parent::unserialize($data);
        $json = json_decode($data);
        $this->repeat = $json->repeat;
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
                . ", exceptionByteArrayId=" . $this->exceptionByteArrayId
                . ", exceptionMessage=" . $this->exceptionMessage
                . ", deploymentId=" . $this->deploymentId
                . "]";
    }
}
