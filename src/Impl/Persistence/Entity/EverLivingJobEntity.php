<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Db\EnginePersistenceLogger;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Util\ClassNameUtil;

class EverLivingJobEntity extends JobEntity
{
    //private final static EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public const TYPE = "ever-living";

    public function getType(): string
    {
        return self::TYPE;
    }

    protected function postExecute(CommandContext $commandContext): void
    {
        //LOG.debugJobExecuted(this);
        $this->init($commandContext);
        $commandContext->getHistoricJobLogManager()->fireJobSuccessfulEvent($this);
    }

    public function init(CommandContext $commandContext, ?bool $shouldResetLock = false): void
    {
        // clean additional data related to this job
        $jobHandler = $this->getJobHandler();
        if ($jobHandler !== null) {
            $jobHandler->onDelete($this->getJobHandlerConfiguration(), $this);
        }

        //cancel the retries -> will resolve job incident if present
        $this->setRetries($commandContext->getProcessEngineConfiguration()->getDefaultNumberOfRetries());

        //delete the job's exception byte array and exception message
        if ($this->exceptionByteArrayId !== null) {
            $this->clearFailedJobException();
        }

        //clean the lock information
        if ($shouldResetLock) {
            $this->setLockOwner(null);
            $this->setLockExpirationTime(null);
        }
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
            'retries' => $this->retries,
            'jobHandlerType' => $this->jobHandlerType,
            'jobHandlerConfiguration' => $this->jobHandlerConfiguration,
            'exceptionByteArray' => serialize($this->exceptionByteArray),
            'exceptionByteArrayId' => $this->exceptionByteArrayId,
            'exceptionMessage' => $this->exceptionMessage,
            'deploymentId' => $this->deploymentId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->revision = $json->revision;
        $this->lockOwner = $json->lockOwner;
        $this->lockExpirationTime = $json->lockExpirationTime;
        $this->executionId = $json->executionId;
        $this->processInstanceId = $json->processInstanceId;
        $this->isExclusive = $json->isExclusive;
        $this->retries = $json->retries;
        $this->jobHandlerType = $json->jobHandlerType;
        $this->jobHandlerConfiguration = $json->jobHandlerConfiguration;
        $this->exceptionByteArray = unserialize($json->exceptionByteArray);
        $this->exceptionByteArrayId = $json->exceptionByteArrayId;
        $this->exceptionMessage = $json->exceptionMessage;
        $this->deploymentId = $json->deploymentId;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
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
