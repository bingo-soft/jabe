<?php

namespace BpmPlatform\Engine\Filter;

use BpmPlatform\Engine\Query\QueryInterface;

interface FilterQueryInterface extends QueryInterface
{
    /**
     * @param filterId set the filter id to query
     * @return this query
     */
    public function filterId(string $filterId): FilterQueryInterface;

    /**
     * @param resourceType set the filter resource type to query
     * @return this query
     */
    public function filterResourceType(string $resourceType): FilterQueryInterface;

    /**
     * @param name set the filter name to query
     * @return this query
     */
    public function filterName(string $name): FilterQueryInterface;

    /**
     * @param nameLike set the filter name like to query
     * @return this query
     */
    public function filterNameLike(string $nameLike): FilterQueryInterface;

    /**
     * @param owner set the filter owner to query
     * @return this query
     */
    public function filterOwner(string $owner): FilterQueryInterface;

    // ordering ////////////////////////////////////////////////////////////

    /** Order by filter id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByFilterId(): FilterQueryInterface;

    /** Order by filter id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByFilterResourceType(): FilterQueryInterface;

    /** Order by filter id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByFilterName(): FilterQueryInterface;

    /** Order by filter id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByFilterOwner(): FilterQueryInterface;
}
