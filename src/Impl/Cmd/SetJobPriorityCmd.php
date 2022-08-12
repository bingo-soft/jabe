<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    JobEntity,
    PropertyChange
};
use Jabe\Impl\Util\EnsureUtil;

class SetJobPriorityCmd implements CommandInterface
{
    public const JOB_PRIORITY_PROPERTY = "priority";
    protected $jobId;
    protected $priority;

    public function __construct(string $jobId, int $priority)
    {
        $this->jobId = $jobId;
        $this->priority = $priority;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("job id must not be null", "jobId", $this->jobId);

        $job = $commandContext->getJobManager()->findJobById($this->jobId);
        EnsureUtil::ensureNotNull("No job found with id '" . $this->jobId . "'", "job", $job);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateJob($job);
        }

        $currentPriority = $job->getPriority();
        $job->setPriority($this->priority);

        //$this->createOpLogEntry($commandContext, $currentPriority, $job);

        return null;
    }

    /*protected function createOpLogEntry(CommandContext $commandContext, int $previousPriority, JobEntity $job): void
    {
        $propertyChange = new PropertyChange(self::JOB_PRIORITY_PROPERTY, $previousPriority, $job->getPriority());
        $commandContext
            ->getOperationLogManager()
            ->logJobOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_SET_PRIORITY,
                $job->getId(),
                $job->getJobDefinitionId(),
                $job->getProcessInstanceId(),
                $job->getProcessDefinitionId(),
                $job->getProcessDefinitionKey(),
                [$propertyChange]
            );
    }*/
}
