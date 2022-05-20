<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineConfiguration;
use Jabe\Engine\Exception\NotValidException;
use Jabe\Engine\History\{
    CleanableHistoricProcessInstanceReportInterface,
    CleanableHistoricProcessInstanceReportResultInterface
};
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class CleanableHistoricProcessInstanceReportImpl extends AbstractQuery implements CleanableHistoricProcessInstanceReportInterface
{
    protected $processDefinitionIdIn = [];
    protected $processDefinitionKeyIn = [];
    protected $tenantIdIn = [];
    protected $isTenantIdSet = false;
    protected $isCompact = false;

    protected $currentTimestamp;

    protected $isHistoryCleanupStrategyRemovalTimeBased;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function processDefinitionIdIn(array $processDefinitionIds): CleanableHistoricProcessInstanceReportInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "processDefinitionIdIn", $processDefinitionIds);
        $this->processDefinitionIdIn = $processDefinitionIds;
        return $this;
    }

    public function processDefinitionKeyIn(array $processDefinitionKeys): CleanableHistoricProcessInstanceReportInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "processDefinitionKeyIn", $processDefinitionKeys);
        $this->processDefinitionKeyIn = $processDefinitionKeys;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): CleanableHistoricProcessInstanceReportInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "tenantIdIn", $tenantIds);
        $this->tenantIdIn = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): CleanableHistoricProcessInstanceReportInterface
    {
        $this->tenantIdIn = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function compact(): CleanableHistoricProcessInstanceReportInterface
    {
        $this->isCompact = true;
        return $this;
    }

    public function orderByFinished(): CleanableHistoricProcessInstanceReportInterface
    {
        $this->orderBy(CleanableHistoricInstanceReportProperty::finishedAmount());
        return $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->provideHistoryCleanupStrategy($commandContext);
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricProcessInstanceManager()
            ->findCleanableHistoricProcessInstancesReportCountByCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->provideHistoryCleanupStrategy($commandContext);
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricProcessInstanceManager()
            ->findCleanableHistoricProcessInstancesReportByCriteria($this, $page);
    }

    public function getCurrentTimestamp(): string
    {
        return $this->currentTimestamp;
    }

    public function setCurrentTimestamp(atring $currentTimestamp): void
    {
        $this->currentTimestamp = $currentTimestamp;
    }

    public function getProcessDefinitionIdIn(): array
    {
        return $this->processDefinitionIdIn;
    }

    public function getProcessDefinitionKeyIn(): array
    {
        return $this->processDefinitionKeyIn;
    }

    public function getTenantIdIn(): array
    {
        return $this->tenantIdIn;
    }

    public function setTenantIdIn(array $tenantIdIn): void
    {
        $this->tenantIdIn = $tenantIdIn;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function isCompact(): bool
    {
        return $this->isCompact;
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
