<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineConfiguration;
use Jabe\Engine\History\{
    CleanableHistoricBatchReportInterface,
    CleanableHistoricBatchReportResultInterface
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};

class CleanableHistoricBatchReportImpl extends AbstractQuery implements CleanableHistoricBatchReportInterface
{
    protected $currentTimestamp;

    protected $isHistoryCleanupStrategyRemovalTimeBased;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function orderByFinishedBatchOperation(): CleanableHistoricBatchReportInterface
    {
        $this->orderBy(CleanableHistoricInstanceReportProperty::finishedAmount());
        return $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->provideHistoryCleanupStrategy($commandContext);

        $this->checkQueryOk();
        $this->checkPermissions($commandContext);

        $batchOperationsForHistoryCleanup = $commandContext->getProcessEngineConfiguration()->getParsedBatchOperationsForHistoryCleanup();

        if ($this->isHistoryCleanupStrategyRemovalTimeBased()) {
            $this->addBatchOperationsWithoutTTL($batchOperationsForHistoryCleanup);
        }

        return $commandContext->getHistoricBatchManager()->findCleanableHistoricBatchesReportCountByCriteria($this, $batchOperationsForHistoryCleanup);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->provideHistoryCleanupStrategy($commandContext);

        $this->checkQueryOk();
        $this->checkPermissions($commandContext);

        $batchOperationsForHistoryCleanup = $commandContext->getProcessEngineConfiguration()->getParsedBatchOperationsForHistoryCleanup();

        if ($this->isHistoryCleanupStrategyRemovalTimeBased()) {
            $this->addBatchOperationsWithoutTTL($batchOperationsForHistoryCleanup);
        }

        return $commandContext->getHistoricBatchManager()->findCleanableHistoricBatchesReportByCriteria($this, $page, $batchOperationsForHistoryCleanup);
    }

    protected function addBatchOperationsWithoutTTL(array $batchOperations): void
    {
        $batchJobHandlers = Context::getProcessEngineConfiguration()->getBatchHandlers();

        $batchOperationKeys = null;
        if (!empty($batchJobHandlers)) {
            $batchOperationKeys = array_keys($batchJobHandlers);
        }

        if ($batchOperationKeys !== null) {
            foreach ($batchOperationKeys as $batchOperation) {
                $ttl = $batchOperations[$batchOperation];
                $batchOperations[$batchOperation] = $ttl;
            }
        }
    }

    public function getCurrentTimestamp(): string
    {
        return $this->currentTimestamp;
    }

    public function setCurrentTimestamp(string $currentTimestamp): void
    {
        $this->currentTimestamp = $currentTimestamp;
    }

    private function checkPermissions(CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadHistoricBatch();
        }
    }

    protected function provideHistoryCleanupStrategy(CommandContext $commandContext): void
    {
        $historyCleanupStrategy = $commandContext->getProcessEngineConfiguration()
            ->getHistoryCleanupStrategy();

        $this->isHistoryCleanupStrategyRemovalTimeBased = ProcessEngineConfiguration::HISTORY_CLEANUP_STRATEGY_REMOVAL_TIME_BASED == $historyCleanupStrategy;
    }

    public function isHistoryCleanupStrategyRemovalTimeBased(): bool
    {
        return $this->isHistoryCleanupStrategyRemovalTimeBased;
    }
}
