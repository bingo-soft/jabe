<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Batch\BatchEntity;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use Jabe\Impl\Persistence\Entity\{
    BatchManager,
    PropertyChange,
    SuspensionState
};
use Jabe\Impl\Util\EnsureUtil;

abstract class AbstractSetBatchStateCmd implements CommandInterface
{
    public const SUSPENSION_STATE_PROPERTY = "suspensionState";

    protected $batchId;

    public function __construct(?string $batchId)
    {
        $this->batchId = $batchId;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("Batch id must not be null", "batch id", $this->batchId);

        $batchManager = $commandContext->getBatchManager();

        $batch = $batchManager->findBatchById($this->batchId);
        EnsureUtil::ensureNotNull("Batch for id '" . $this->batchId . "' cannot be found", "batch", $batch);

        $this->checkAccess($commandContext, $batch);

        $this->setJobDefinitionState($commandContext, $batch->getSeedJobDefinitionId());
        $this->setJobDefinitionState($commandContext, $batch->getMonitorJobDefinitionId());
        $this->setJobDefinitionState($commandContext, $batch->getBatchJobDefinitionId());

        $batchManager->updateBatchSuspensionStateById($this->batchId, $this->getNewSuspensionState());

        $this->logUserOperation($commandContext);

        return null;
    }

    abstract public function getNewSuspensionState(): SuspensionState;

    protected function checkAccess($ctx, BatchEntity $batch): void
    {
        if ($ctx instanceof CommandContext) {
            foreach ($ctx->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                $this->checkAccess($checker, $batch);
            }
        }
    }

    protected function setJobDefinitionState(CommandContext $commandContext, ?string $jobDefinitionId): void
    {
        $this->createSetJobDefinitionStateCommand($jobDefinitionId)->execute($commandContext);
    }

    protected function createSetJobDefinitionStateCommand($data): AbstractSetJobDefinitionStateCmd
    {
        if (is_string($data)) {
            $suspendJobDefinitionCmd = $this->createSetJobDefinitionStateCommand(
                (new UpdateJobDefinitionSuspensionStateBuilderImpl())
                ->byJobDefinitionId($data)
                ->includeJobs(true)
            );
            $suspendJobDefinitionCmd->disableLogUserOperation();
            return $suspendJobDefinitionCmd;
        }
    }

    protected function logUserOperation(CommandContext $commandContext): void
    {
        $propertyChange = new PropertyChange(self::SUSPENSION_STATE_PROPERTY, null, $this->getNewSuspensionState()->getName());
        $commandContext->getOperationLogManager()
            ->logBatchOperation($this->getUserOperationType(), $this->batchId, $propertyChange);
    }

    abstract protected function getUserOperationType(): ?string;

    public function isRetryable(): bool
    {
        return false;
    }
}
