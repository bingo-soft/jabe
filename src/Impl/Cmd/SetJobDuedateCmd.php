<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    PropertyChange,
    TimerEntity
};

class SetJobDuedateCmd implements CommandInterface, \Serializable
{
    private $jobId;
    private $newDuedate;
    private $cascade;

    public function __construct(?string $jobId, ?string $newDuedate, bool $cascade)
    {
        if (empty($jobId)) {
            throw new ProcessEngineException("The job id is mandatory, but '" . $jobId . "' has been provided.");
        }
        $this->jobId = $jobId;
        $this->newDuedate = $newDuedate;
        $this->cascade = $cascade;
    }

    public function serialize()
    {
        return json_encode([
            'jobId' => $this->jobId,
            'newDuedate' => $this->newDuedate,
            'cascade' => $this->cascade
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->jobId = $json->jobId;
        $this->newDuedate = $json->newDuedate;
        $this->cascade = $json->cascade;
    }

    public function execute(CommandContext $commandContext)
    {
        $job = $commandContext->getJobManager()
                ->findJobById($this->jobId);
        if ($job !== null) {
            foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                $checker->checkUpdateJob($job);
            }

            $commandContext->getOperationLogManager()->logJobOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_SET_DUEDATE,
                $this->jobId,
                $job->getJobDefinitionId(),
                $job->getProcessInstanceId(),
                $job->getProcessDefinitionId(),
                $job->getProcessDefinitionKey(),
                [new PropertyChange("duedate", $job->getDuedate(), $this->newDuedate)]
            );

            // for timer jobs cascade due date changes
            if ($this->cascade && $this->newDuedate !== null && $job instanceof TimerEntity) {
                $offset = (new \DateTime($this->newDuedate))->format('Uv') - (new \DateTime($job->getDuedate()))->format('Uv');
                $job->setRepeatOffset($job->getRepeatOffset() + $offset);
            }
            $job->setDuedate($this->newDuedate);
        } else {
            throw new ProcessEngineException("No job found with id '" . $this->jobId . "'.");
        }
        return null;
    }
}
