<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\{
    CompareUtil,
    EnsureUtil
};
use Jabe\Repository\DeploymentQueryInterface;

class DeploymentQueryImpl extends AbstractQuery implements DeploymentQueryInterface
{
    protected $deploymentId;
    protected $name;
    protected $nameLike;
    protected bool $sourceQueryParamEnabled = false;
    protected $source;
    protected $deploymentBefore;
    protected $deploymentAfter;

    protected bool $isTenantIdSet = false;
    protected $tenantIds = [];
    protected bool $includeDeploymentsWithoutTenantId = false;

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function __serialize(): array
    {
        return [
            'deploymentId' => $this->deploymentId,
            'name' => $this->name,
            'nameLike' => $this->nameLike,
            'sourceQueryParamEnabled' => $this->sourceQueryParamEnabled,
            'source' => $this->source,
            'deploymentBefore' => $this->deploymentBefore,
            'deploymentAfter' => $this->deploymentAfter,
            'isTenantIdSet' => $this->isTenantIdSet,
            'tenantIds' => $this->tenantIds,
            'includeDeploymentsWithoutTenantId' => $this->includeDeploymentsWithoutTenantId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->deploymentId = $data['deploymentId'];
        $this->name = $data['name'];
        $this->nameLike = $data['nameLike'];
        $this->sourceQueryParamEnabled = $data['sourceQueryParamEnabled'];
        $this->source = $data['source'];
        $this->deploymentBefore = $data['deploymentBefore'];
        $this->deploymentAfter = $data['deploymentAfter'];
        $this->isTenantIdSet = $data['isTenantIdSet'];
        $this->tenantIds = $data['tenantIds'];
        $this->includeDeploymentsWithoutTenantId = $data['includeDeploymentsWithoutTenantId'];
    }

    public function deploymentId(?string $deploymentId): DeploymentQueryImpl
    {
        EnsureUtil::ensureNotNull("Deployment id", "Deployment id", $deploymentId);
        $this->deploymentId = $deploymentId;
        return $this;
    }

    public function deploymentName(?string $deploymentName): DeploymentQueryImpl
    {
        EnsureUtil::ensureNotNull("deploymentName", "deploymentName", $deploymentName);
        $this->name = $deploymentName;
        return $this;
    }

    public function deploymentNameLike(?string $nameLike): DeploymentQueryImpl
    {
        EnsureUtil::ensureNotNull("deploymentNameLike", "deploymentNameLike", $nameLike);
        $this->nameLike = $nameLike;
        return $this;
    }

    public function deploymentSource(?string $source): DeploymentQueryInterface
    {
        $this->sourceQueryParamEnabled = true;
        $this->source = $source;
        return $this;
    }

    public function deploymentBefore(?string $before): DeploymentQueryInterface
    {
        EnsureUtil::ensureNotNull("deploymentBefore", "deploymentBefore", $before);
        $this->deploymentBefore = $before;
        return $this;
    }

    public function deploymentAfter(?string $after): DeploymentQueryInterface
    {
        EnsureUtil::ensureNotNull("deploymentAfter", "deploymentAfter", $after);
        $this->deploymentAfter = $after;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): DeploymentQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): DeploymentQueryInterface
    {
        $this->isTenantIdSet = true;
        $this->tenantIds = null;
        return $this;
    }

    public function includeDeploymentsWithoutTenantId(): DeploymentQueryInterface
    {
        $this->includeDeploymentsWithoutTenantId  = true;
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions() || CompareUtil::areNotInAscendingOrder($this->deploymentAfter, $this->deploymentBefore);
    }

    //sorting ////////////////////////////////////////////////////////

    public function orderByDeploymentId(): DeploymentQueryInterface
    {
        return $this->orderBy(DeploymentQueryProperty::deploymentId());
    }

    public function orderByDeploymenTime(): DeploymentQueryInterface
    {
        return $this->orderBy(DeploymentQueryProperty::deployTime());
    }

    public function orderByDeploymentTime(): DeploymentQueryInterface
    {
        return $this->orderBy(DeploymentQueryProperty::deployTime());
    }

    public function orderByDeploymentName(): DeploymentQueryInterface
    {
        return $this->orderBy(DeploymentQueryProperty::deploymentName());
    }

    public function orderByTenantId(): DeploymentQueryInterface
    {
        return $this->orderBy(DeploymentQueryProperty::tenantId());
    }

    //results ////////////////////////////////////////////////////////

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getDeploymentManager()
            ->findDeploymentCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getDeploymentManager()
            ->findDeploymentsByQueryCriteria($this, $page);
    }

    //getters ////////////////////////////////////////////////////////

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameLike(): ?string
    {
        return $this->nameLike;
    }

    public function isSourceQueryParamEnabled(): bool
    {
        return $this->sourceQueryParamEnabled;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getDeploymentBefore(): ?string
    {
        return $this->deploymentBefore;
    }

    public function getDeploymentAfter(): ?string
    {
        return $this->deploymentAfter;
    }
}
