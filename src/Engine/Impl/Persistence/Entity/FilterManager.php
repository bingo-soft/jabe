<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Authorization\{
    AuthorizationInterface,
    Permissions,
    Resources
};
use Jabe\Engine\Filter\FilterInterface;
use Jabe\Engine\Impl\{
    AbstractQuery,
    StoredQueryValidator
};
use Jabe\Engine\Impl\Filter\FilterQueryImpl;
use Jabe\Engine\Impl\Persistence\AbstractManager;
use Jabe\Engine\Impl\Util\EnsureUtil;

class FilterManager extends AbstractManager
{
    public function createNewFilter(string $resourceType): FilterInterface
    {
        $this->checkAuthorization(Permissions::create(), Resources::filter(), AuthorizationInterface::ANY);
        return new FilterEntity($resourceType);
    }

    public function insertOrUpdateFilter(FilterInterface $filter): FilterInterface
    {
        $query = $filter->getQuery();
        $query->validate(StoredQueryValidator::get());

        if ($filter->getId() == null) {
            $this->checkAuthorization(Permissions::create(), Resources::filter(), AuthorizationInterface::ANY);
            $this->getDbEntityManager()->insert($filter);
            $this->createDefaultAuthorizations($filter);
        } else {
            $this->checkAuthorization(Permissions::update(), Resources::filter(), $filter->getId());
            $this->getDbEntityManager()->merge($filter);
        }

        return $filter;
    }

    public function deleteFilter(string $filterId): void
    {
        $this->checkAuthorization(Permissions::delete(), Resources::filter(), $filterId);

        $filter = $this->findFilterByIdInternal($filterId);
        EnsureUtil::ensureNotNull("No filter found for filter id '" . $filterId . "'", "filter", $filter);

        // delete all authorizations for this filter id
        $this->deleteAuthorizations(Resources::filter(), $filterId);
        // delete the filter itself
        $this->getDbEntityManager()->delete($filter);
    }

    public function findFilterById(string $filterId): FilterEntity
    {
        EnsureUtil::ensureNotNull("Invalid filter id", "filterId", $filterId);
        $this->checkAuthorization(Permissions::read(), Resources::filter(), $filterId);
        return $this->findFilterByIdInternal($filterIdfilterId);
    }

    protected function findFilterByIdInternal(string $filterId): FilterEntity
    {
        return $this->getDbEntityManager()->selectById(FilterEntity::class, $filterId);
    }

    public function findFiltersByQueryCriteria(FilterQueryImpl $filterQuery): array
    {
        $this->configureQuery($filterQuery, Resources::filter());
        return $this->getDbEntityManager()->selectList("selectFilterByQueryCriteria", $filterQuery);
    }

    public function findFilterCountByQueryCriteria(FilterQueryImpl $filterQuery): int
    {
        $this->configureQuery($filterQuery, Resources::filter());
        return $this->getDbEntityManager()->selectOne("selectFilterCountByQueryCriteria", $filterQuery);
    }

    // authorization utils /////////////////////////////////

    protected function createDefaultAuthorizations(FilterInterface $filter): void
    {
        if ($this->isAuthorizationEnabled()) {
            $this->saveDefaultAuthorizations($this->getResourceAuthorizationProvider()->newFilter($filter));
        }
    }
}
