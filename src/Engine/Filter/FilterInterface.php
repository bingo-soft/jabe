<?php

namespace BpmPlatform\Engine\Filter;

use BpmPlatform\Engine\Query\QueryInterface;

interface FilterInterface
{
    /**
     * @return the id of the filer
     */
    public function getId(): string;

    /**
     * @return the resource type fo the filter
     */
    public function getResourceType(): string;

    /**
     * @return the name of the filter
     */
    public function getName(): string;

    /**
     * @param name the name of the filter
     * @return this filter
     */
    public function setName(string $name): FilterInterface;

    /**
     * @return the owner of the filter
     */
    public function getOwner(): string;

    /**
     * @param owner the owner of the filter
     * @return this filter
     */
    public function setOwner(string $owner): FilterInterface;

    /**
     * @return the saved query as query object
     */
    public function getQuery(): QueryInterface;

    /**
     * @param query the saved query as query object
     * @return this filter
     */
    public function setQuery(QueryInterface $query): FilterInterface;

    /**
     * Extends the query with the additional query. The query of the filter is therefore modified
     * and if the filter is saved the query is updated.
     *
     * @param extendingQuery the query to extend the filter with
     * @return a copy of this filter with the extended query
     */
    public function extend(QueryInterface $extendingQuery): FilterInterface;

    /**
     * @return the properties as map
     */
    public function getProperties(): array;

    /**
     * @param properties the properties to set as map
     * @return this filter
     */
    public function setProperties(array $properties): FilterInterface;
}
