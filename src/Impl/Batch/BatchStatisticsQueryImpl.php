<?php

namespace Jabe\Impl\Batch;

use Jabe\Batch\{
    BatchStatisticsInterface,
    BatchStatisticsQueryInterface
};
use Jabe\Impl\{
    AbstractQuery,
    BatchQueryProperty,
    Page
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Util\EnsureUtil;

class BatchStatisticsQueryImpl extends AbstractQuery implements BatchStatisticsQueryInterface
{
    protected $batchId;
    protected $type;
    protected $isTenantIdSet = false;
    protected $tenantIds = [];
    protected $suspensionState;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function batchId(string $batchId): BatchStatisticsQueryInterface
    {
        EnsureUtil::ensureNotNull("Batch id", "batchId", $batchId);
        $this->batchId = $batchId;
        return $this;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function type(string $type): BatchStatisticsQueryInterface
    {
        EnsureUtil::ensureNotNull("Type", "type", $type);
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function tenantIdIn(array $tenantIds): BatchStatisticsQueryInterface
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

    public function withoutTenantId(): BatchStatisticsQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function active(): BatchStatisticsQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function suspended(): BatchStatisticsQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    public function getSuspensionState(): SuspensionState
    {
        return $this->suspensionState;
    }

    public function orderById(): BatchStatisticsQueryInterface
    {
        return $this->orderBy(BatchQueryProperty::id());
    }

    public function orderByTenantId(): BatchStatisticsQueryInterface
    {
        return $this->orderBy(BatchQueryProperty::tenantId());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getStatisticsManager()
            ->getStatisticsCountGroupedByBatch($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getStatisticsManager()
            ->getStatisticsGroupedByBatch($this, $page);
    }
}
