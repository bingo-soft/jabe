<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\{
    JobEntity,
    PropertyChange
};

class AbstractSetJobRetriesCmd
{
    protected const RETRIES = "retries";

    protected function setJobRetriesByJobId(?string $jobId, int $retries, CommandContext $commandContext): void
    {
        $job = $commandContext
            ->getJobManager()
            ->findJobById($jobId);
        if ($job !== null) {
            foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                $checker->checkUpdateRetriesJob($job);
            }

            if ($job->isInInconsistentLockState()) {
                $job->resetLock();
            }
            $oldRetries = $job->getRetries();
            $job->setRetries($retries);

            $propertyChange = new PropertyChange(self::RETRIES, $oldRetries, $job->getRetries());
            $commandContext->getOperationLogManager()->logJobOperation(
                $this->getLogEntryOperation(),
                $job->getId(),
                $job->getJobDefinitionId(),
                $job->getProcessInstanceId(),
                $job->getProcessDefinitionId(),
                $job->getProcessDefinitionKey(),
                $propertyChange
            );
        } else {
            throw new ProcessEngineException("No job found with id '" . $jobId . "'.");
        }
    }

    protected function getLogEntryOperation(): ?string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_SET_JOB_RETRIES;
    }
}
