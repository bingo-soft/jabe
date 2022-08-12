<?php

namespace Jabe\Impl\Filter;

use Jabe\Filter\FilterQueryInterface;
use Jabe\Impl\{
    AbstractQuery,
    Page
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;

class FilterQueryImpl extends AbstractQuery implements FilterQueryInterface
{
    protected $filterId;
    protected $resourceType;
    protected $name;
    protected $nameLike;
    protected $owner;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function filterId(string $filterId): FilterQueryInterface
    {
        EnsureUtil::ensureNotNull("filterId", "filterId", $filterId);
        $this->filterId = $filterId;
        return $this;
    }

    public function filterResourceType(string $resourceType): FilterQueryInterface
    {
        EnsureUtil::ensureNotNull("resourceType", "resourceType", $resourceType);
        $this->resourceType = $resourceType;
        return $this;
    }

    public function filterName(string $name): FilterQueryInterface
    {
        EnsureUtil::ensureNotNull("name", "name", $name);
        $this->name = $name;
        return $this;
    }

    public function filterNameLike(string $nameLike): FilterQueryInterface
    {
        EnsureUtil::ensureNotNull("nameLike", "nameLike", $nameLike);
        $this->nameLike = $nameLike;
        return $this;
    }

    public function filterOwner(string $owner): FilterQueryInterface
    {
        EnsureUtil::ensureNotNull("owner", "owner", $owner);
        $this->owner = $owner;
        return $this;
    }

    public function orderByFilterId(): FilterQueryInterface
    {
        return $this->orderBy(FilterQueryProperty::filterId());
    }

    public function orderByFilterResourceType(): FilterQueryInterface
    {
        return $this->orderBy(FilterQueryProperty::resourceType());
    }

    public function orderByFilterName(): FilterQueryInterface
    {
        return $this->orderBy(FilterQueryProperty::name());
    }

    public function orderByFilterOwner(): FilterQueryInterface
    {
        return $this->orderBy(FilterQueryProperty::owner());
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        return $commandContext
            ->getFilterManager()
            ->findFiltersByQueryCriteria($this);
    }

    public function executeCount(CommandContext $commandContext): int
    {
        return $commandContext
            ->getFilterManager()
            ->findFilterCountByQueryCriteria($this);
    }
}
