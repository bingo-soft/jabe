<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
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

class DeleteJobCmd implements CommandInterface, \Serializable
{
    protected $jobId;

    public function __construct(?string $jobId)
    {
        $this->jobId = $jobId;
    }

    public function serialize()
    {
        return json_encode([
            'jobId' => $this->jobId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->jobId = $json->jobId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("jobId", "jobId", $this->jobId);

        $job = $commandContext->getJobManager()->findJobById($this->jobId);
        EnsureUtil::ensureNotNull("No job found with id '" . $this->jobId . "'", "job", $job);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateJob($job);
        }
        // We need to check if the job was locked, ie acquired by the job acquisition thread
        // This happens if the the job was already acquired, but not yet executed.
        // In that case, we can't allow to delete the job.
        if ($job->getLockOwner() !== null || $job->getLockExpirationTime() !== null) {
            throw new ProcessEngineException("Cannot delete job when the job is being executed. Try again later.");
        }

        $commandContext->getOperationLogManager()->logJobOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_DELETE,
            $this->jobId,
            $job->getJobDefinitionId(),
            $job->getProcessInstanceId(),
            $job->getProcessDefinitionId(),
            $job->getProcessDefinitionKey(),
            PropertyChange::emptyChange()
        );

        $job->delete();
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
