<?php

namespace Jabe\Impl\Cmd;

use Concurrent\ThreadInterface;
use Jabe\ProcessEngineException;
use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Context\ProcessApplicationContextUtil;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\JobExecutor\{
    TimerCatchIntermediateEventJobHandler,
    TimerDeclarationImpl,
    TimerExecuteNestedActivityJobHandler,
    TimerStartEventJobHandler,
    TimerStartEventSubprocessJobHandler,
    TimerTaskListenerJobHandler,
    TimerJobConfiguration
};
use Jabe\Impl\Persistence\Entity\{
    JobEntity,
    PropertyChange,
    TimerEntity
};
use Jabe\Impl\Pvm\Process\ActivityImpl;
use Concurrent\RunnableInterface;
use Jabe\Impl\Util\EnsureUtil;

class RecalculateJobDuedateCmd implements CommandInterface
{
    private $jobId;
    private $creationDateBased;

    public function __construct(?string $jobId, bool $creationDateBased)
    {
        EnsureUtil::ensureNotEmpty("The job id is mandatory", "jobId", $jobId);
        $this->jobId = $jobId;
        $this->creationDateBased = $creationDateBased;
    }

    public function __serialize(): array
    {
        return [
            'jobId' => $this->jobId,
            'creationDateBased' => $this->creationDateBased
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->jobId = $data['jobId'];
        $this->creationDateBased = $data['creationDateBased'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $job = $commandContext->getJobManager()->findJobById($this->jobId);
        EnsureUtil::ensureNotNull("No job found with id '" . $this->jobId . "'", "job", $job);

        // allow timer jobs only
        $this->checkJobType($job);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateJob($job);
        }

        // prepare recalculation
        $timerDeclaration = $this->findTimerDeclaration($commandContext, $job);
        $timer = $job;
        $oldDuedate = $job->getDuedate();
        $creationDateBased = $this->creationDateBased;
        $runnable = new class ($timerDeclaration, $timer, $creationDateBased) implements RunnableInterface {
            private $timerDeclaration;
            private $timer;
            private $creationDateBased;

            public function __construct($timerDeclaration, $timer, $creationDateBased)
            {
                $this->timerDeclaration = $timerDeclaration;
                $this->timer = $timer;
                $this->creationDateBased = $creationDateBased;
            }

            public function run(ThreadInterface $process = null, ...$args): void
            {
                $this->timerDeclaration->resolveAndSetDuedate($this->timer->getExecution(), $this->timer, $this->creationDateBased);
            }
        };

        // run recalculation in correct context
        $contextDefinition = $commandContext
            ->getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findDeployedProcessDefinitionById($job->getProcessDefinitionId());
        ProcessApplicationContextUtil::doContextSwitch($runnable, $contextDefinition);

        // log operation
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("duedate", $oldDuedate, $job->getDuedate());
        $propertyChanges[] = new PropertyChange("creationDateBased", null, $this->creationDateBased);
        $commandContext->getOperationLogManager()->logJobOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_RECALC_DUEDATE,
            $this->jobId,
            $job->getJobDefinitionId(),
            $job->getProcessInstanceId(),
            $job->getProcessDefinitionId(),
            $job->getProcessDefinitionKey(),
            $propertyChanges
        );

        return null;
    }

    protected function checkJobType(JobEntity $job): void
    {
        $type = $job->getJobHandlerType();
        if (
            !(TimerExecuteNestedActivityJobHandler::TYPE == $type ||
            TimerCatchIntermediateEventJobHandler::TYPE == $type ||
            TimerStartEventJobHandler::TYPE == $type ||
            TimerStartEventSubprocessJobHandler::TYPE == $type ||
            TimerTaskListenerJobHandler::TYPE == $type) ||
            !($job instanceof TimerEntity)
        ) {
            throw new ProcessEngineException("Only timer jobs can be recalculated, but the job with id '" . $this->jobId . "' is of type '" . $type . "'.");
        }
    }

    protected function findTimerDeclaration(CommandContext $commandContext, JobEntity $job): ?TimerDeclarationImpl
    {
        $timerDeclaration = null;
        if ($job->getExecutionId() !== null) {
            // timeout listener or boundary / intermediate / subprocess start event
            $timerDeclaration = $this->findTimerDeclarationForActivity($commandContext, $job);
        } else {
            // process instance start event
            $timerDeclaration = $this->findTimerDeclarationForProcessStartEvent($commandContext, $job);
        }

        if ($timerDeclaration === null) {
            throw new ProcessEngineException("No timer declaration found for job id '" . $this->jobId . "'.");
        }
        return $timerDeclaration;
    }

    protected function findTimerDeclarationForActivity(CommandContext $commandContext, JobEntity $job): ?TimerDeclarationImpl
    {
        $execution = $commandContext->getExecutionManager()->findExecutionById($job->getExecutionId());
        if ($execution === null) {
            throw new ProcessEngineException("No execution found with id '" . $job->getExecutionId() . "' for job id '" . $this->jobId . "'.");
        }
        $activity = $execution->getProcessDefinition()->findActivity($job->getActivityId());
        if ($activity !== null) {
            if (TimerTaskListenerJobHandler::TYPE == $job->getJobHandlerType()) {
                return $this->findTimeoutListenerDeclaration($job, $activity);
            }
            $timerDeclarations = TimerDeclarationImpl::getDeclarationsForScope($activity->getEventScope());
            if (!empty($timerDeclarations) && array_key_exists($job->getActivityId(), $timerDeclarations)) {
                return  $timerDeclarations[$job->getActivityId()];
            }
        }
        return null;
    }

    protected function findTimeoutListenerDeclaration(JobEntity $job, ActivityImpl $activity): ?TimerDeclarationImpl
    {
        $timeoutDeclarations = TimerDeclarationImpl::getTimeoutListenerDeclarationsForScope($activity->getEventScope());
        if (!empty($timeoutDeclarations)) {
            if (array_key_exists($job->getActivityId(), $timeoutDeclarations)) {
                $activityTimeouts = $timeoutDeclarations[$job->getActivityId()];
                if (!empty($activityTimeouts)) {
                    $jobHandlerConfiguration = $job->getJobHandlerConfiguration();
                    if ($jobHandlerConfiguration instanceof TimerJobConfiguration) {
                        return $activityTimeouts[strval($jobHandlerConfiguration)]->getTimerElementSecondaryKey();
                    }
                }
            }
        }
        return null;
    }

    protected function findTimerDeclarationForProcessStartEvent(CommandContext $commandContext, JobEntity $job): ?TimerDeclarationImpl
    {
        $processDefinition = $commandContext->getProcessEngineConfiguration()->getDeploymentCache()->findDeployedProcessDefinitionById($job->getProcessDefinitionId());
        $timerDeclarations = $processDefinition->getProperty(BpmnParse::PROPERTYNAME_START_TIMER);
        foreach ($timerDeclarations as $timerDeclarationCandidate) {
            if ($timerDeclarationCandidate->getJobDefinitionId() == $job->getJobDefinitionId()) {
                return $timerDeclarationCandidate;
            }
        }
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
