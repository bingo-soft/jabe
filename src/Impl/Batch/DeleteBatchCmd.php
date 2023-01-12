<?php

namespace Jabe\Impl\Batch;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\PropertyChange;
use Jabe\Impl\Util\EnsureUtil;

class DeleteBatchCmd implements CommandInterface
{
    protected $cascadeToHistory;
    protected $batchId;

    public function __construct(?string $batchId, bool $cascadeToHistory)
    {
        $this->batchId = $batchId;
        $this->cascadeToHistory = $cascadeToHistory;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("Batch id must not be null", "batch id", $this->batchId);

        $batchEntity = $commandContext->getBatchManager()->findBatchById($this->batchId);
        EnsureUtil::ensureNotNull("Batch for id '" . $this->batchId . "' cannot be found", "batch", $batchEntity);

        $this->checkAccess($commandContext, $batchEntity);
        $this->writeUserOperationLog($commandContext);
        $batchEntity->delete($this->cascadeToHistory, true);

        return null;
    }

    protected function checkAccess(CommandContext $commandContext, BatchEntity $batch): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteBatch($batch);
        }
    }

    protected function writeUserOperationLog(CommandContext $commandContext): void
    {
        $commandContext->getOperationLogManager()
            ->logBatchOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_DELETE,
                $this->batchId,
                new PropertyChange("cascadeToHistory", null, $this->cascadeToHistory)
            );
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
