<?php

namespace Jabe\Impl\Batch;

use Jabe\Batch\{
    BatchInterface,
    BatchQueryInterface
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

class BatchQueryImpl extends AbstractQuery implements BatchQueryInterface
{
    protected $batchId;
    protected $type;
    protected bool $isTenantIdSet = false;
    protected $tenantIds = [];
    protected $suspensionState;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function batchId(?string $batchId): BatchQueryInterface
    {
        EnsureUtil::ensureNotNull("Batch id", "batchId", $batchId);
        $this->batchId = $batchId;
        return $this;
    }

    public function getBatchId(): ?string
    {
        return $this->batchId;
    }

    public function type(?string $type): BatchQueryInterface
    {
        EnsureUtil::ensureNotNull("Type", "type", $type);
        $this->type = $type;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function tenantIdIn(array $tenantIds): BatchQueryInterface
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

    public function withoutTenantId(): BatchQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function active(): BatchQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function suspended(): BatchQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    public function getSuspensionState(): SuspensionState
    {
        return $this->suspensionState;
    }

    public function orderById(): BatchQueryInterface
    {
        return $this->orderBy(BatchQueryProperty::id());
    }

    public function orderByTenantId(): BatchQueryInterface
    {
        return $this->orderBy(BatchQueryProperty::tenantId());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext->getBatchManager()
            ->findBatchCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext->getBatchManager()
            ->findBatchesByQueryCriteria($this, $page);
    }
}
