<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger
};
use Jabe\Impl\Db\EntityManager\{
    OptimisticLockingListenerInterface,
    OptimisticLockingResult
};
use Jabe\Impl\Db\EntityManager\Operation\{
    DbEntityOperation,
    DbOperation
};
use Jabe\Impl\ExternalTask\{
    LockedExternalTaskImpl,
    TopicFetchInstruction
};
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExternalTaskEntity
};
use Jabe\Impl\Util\EnsureUtil;

class FetchExternalTasksCmd implements CommandInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $workerId;
    protected int $maxResults = 0;
    protected bool $usePriority = false;
    protected $fetchInstructions = [];

    public function __construct(?string $workerId, int $maxResults, array $instructions, ?bool $usePriority = false)
    {
        $this->workerId = $workerId;
        $this->maxResults = $maxResults;
        $this->fetchInstructions = $instructions;
        $this->usePriority = $usePriority;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $this->validateInput();

        foreach (array_values($this->fetchInstructions) as $instruction) {
            $instruction->ensureVariablesInitialized();
        }

        $externalTasks = $commandContext
            ->getExternalTaskManager()
            ->selectExternalTasksForTopics(array_values($this->fetchInstructions), $this->maxResults, $this->usePriority);

        $result = [];

        foreach ($externalTasks as $entity) {
            $fetchInstruction = $this->fetchInstructions[$entity->getTopicName()];

            // retrieve the execution first to detect concurrent modifications @https://jira.camunda.com/browse/CAM-10750
            $execution = $entity->getExecution(false);

            if ($execution !== null) {
                $entity->lock($this->workerId, $fetchInstruction->getLockDuration());
                $resultTask = LockedExternalTaskImpl::fromEntity(
                    $entity,
                    $fetchInstruction->getVariablesToFetch(),
                    $fetchInstruction->isLocalVariables(),
                    $fetchInstruction->isDeserializeVariables(),
                    $fetchInstruction->isIncludeExtensionProperties()
                );
                $result[] = $resultTask;
            } else {
                //LOG.logTaskWithoutExecution(workerId);
            }
        }

        $this->filterOnOptimisticLockingFailure($commandContext, $result);

        return $result;
    }

    public function isRetryable(): bool
    {
        return true;
    }

    protected function filterOnOptimisticLockingFailure(CommandContext $commandContext, array $tasks): void
    {
        $commandContext->getDbEntityManager()->registerOptimisticLockingListener(new class ($tasks) implements OptimisticLockingListenerInterface {

            private $tasks;

            public function __construct(array $tasks)
            {
                $this->tasks = $tasks;
            }

            public function getEntityType(): ?string
            {
                return ExternalTaskEntity::class;
            }

            public function failedOperation(DbOperation $operation): ?string
            {

                if ($operation instanceof DbEntityOperation) {
                    $dbEntityOperation = $operation;
                    $dbEntity = $dbEntityOperation->getEntity();

                    $failedOperationEntityInList = false;

                    foreach ($this->tasks as $key => $task) {
                        $resultTask = $task;
                        if ($resultTask->getId() == $dbEntity->getId()) {
                            unset($this->tasks[$key]);
                            $failedOperationEntityInList = true;
                            break;
                        }
                    }

                    // If the entity that failed with an OLE is not in the list,
                    // we rethrow the OLE to the caller.
                    if (!$failedOperationEntityInList) {
                        return OptimisticLockingResult::THROW;
                    }

                    // If the entity that failed with an OLE has been removed
                    // from the list, we suppress the OLE.
                    return OptimisticLockingResult::IGNORE;
                }

                // If none of the conditions are satisfied, this might indicate a bug,
                // so we throw the OLE.
                return OptimisticLockingResult::THROW;
            }
        });
    }

    protected function validateInput(): void
    {
        EnsureUtil::ensureNotNull("workerId", "workerId", $this->workerId);
        EnsureUtil::ensureGreaterThanOrEqual("Max results cannot be negative", "maxResults", $this->maxResults, 0);

        foreach (array_values($this->fetchInstructions) as $instruction) {
            EnsureUtil::ensureNotNull("topicName", "topicName", $instruction->getTopicName());
            EnsureUtil::ensurePositive("lockTime", "lockTime", $instruction->getLockDuration());
        }
    }
}
