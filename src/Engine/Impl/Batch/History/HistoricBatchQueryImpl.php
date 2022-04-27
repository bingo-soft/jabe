<?php

namespace Jabe\Engine\Impl\Batch\History;

use Jabe\Engine\Batch\HistoricBatchQueryInterface;
use Jabe\Engine\Impl\{
    HistoricBatchQueryProperty,
    Page
};
use Jabe\Engine\Impl\Interceptor\{
    CommandExecutorInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class HistoricBatchQueryImpl extends AbstractQuery implements HistoricBatchQueryInterface
{
    protected $batchId;
    protected $type;
    protected $completed;
    protected $isTenantIdSet = false;
    protected $tenantIds = [];

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function batchId(string $batchId): HistoricBatchQueryInterface
    {
        EnsureUtil::ensureNotNull("Batch id", "Batch id", $batchId);
        $this->batchId = $batchId;
        return $this;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function type(string $type): HistoricBatchQueryInterface
    {
        EnsureUtil::ensureNotNull("Type", "Type", $type);
        $this->type = $type;
        return $this;
    }

    public function completed(bool $completed): HistoricBatchQueryInterface
    {
        $this->completed = $completed;
        return $this;
    }

    public function tenantIdIn(array $tenantIds = null): HistoricBatchQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function withoutTenantId(): HistoricBatchQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function orderById(): HistoricBatchQueryInterface
    {
        return $this->orderBy(HistoricBatchQueryProperty::id());
    }

    public function orderByStartTime(): HistoricBatchQueryInterface
    {
        return $this->orderBy(HistoricBatchQueryProperty::startTime());
    }

    public function orderByEndTime(): HistoricBatchQueryInterface
    {
        return $this->orderBy(HistoricBatchQueryProperty::endTime());
    }

    public function orderByTenantId(): HistoricBatchQueryInterface
    {
        return $this->orderBy(HistoricBatchQueryProperty::tenantId());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricBatchManager()
            ->findBatchCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricBatchManager()
            ->findBatchesByQueryCriteria($this, $page);
    }
}
