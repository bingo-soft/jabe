<?php

namespace BpmPlatform\Engine;

use BpmPlatform\Engine\Filter\{
    FilterInterface,
    FilterQueryInterface
};
use BpmPlatform\Engine\Query\QueryInterface;

interface FilterServiceInterface
{
    /**
     * Creates a new task filter with a given name.
     *
     * @return a new task filter with a name
     * @throws AuthorizationException if the user has no {@link Permissions#CREATE} permissions on {@link Resources#FILTER}.
     */
    public function newTaskFilter(?string $filterName = null): FilterInterface;

    /**
     * Creates a new filter query
     *
     * @return a new query for filters
     */
    public function createFilterQuery(): FilterQueryInterface;


    /**
     * Creates a new task filter query.
     *
     * @return a new query for task filters
     */
    public function createTaskFilterQuery(): FilterQueryInterface;

    /**
     * Saves the filter in the database.
     *
     * @param filter the filter to save
     * @return return the saved filter
     * @throws AuthorizationException if the user has no {@link Permissions#CREATE} permissions on {@link Resources#FILTER} (save new filter)
     * or if user has no {@link Permissions#UPDATE} permissions on {@link Resources#FILTER} (update existing filter).
     * @throws BadUserRequestException
     *  <ul><li>When the filter query uses expressions and expression evaluation is deactivated for stored queries.
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function saveFilter(FilterInterface $filter): FilterInterface;

    /**
     * Returns the filter for the given filter id.
     *
     * @param filterId the id of the filter
     * @return the filter
     * @throws AuthorizationException if the user has no {@link Permissions#READ} permissions on {@link Resources#FILTER}.
     */
    public function getFilter(string $filterId): ?FilterInterface;

    /**
     * Deletes a filter by its id.
     *
     * @param filterId the id of the filter
     * @throws AuthorizationException if the user has no {@link Permissions#DELETE} permissions on {@link Resources#FILTER}.
     */
    public function deleteFilter(string $filterId): void;

    /**
     * Executes the extended query of a filter and returns the result as list.
     *
     * @param filterId the id of the filter
     * @param extendingQuery additional query to extend the filter query
     * @return the query result as list
     * @throws AuthorizationException if the user has no {@link Permissions#READ} permissions on {@link Resources#FILTER}.
     * @throws BadUserRequestException
     *   <ul><li>When the filter query uses expressions and expression evaluation is deactivated for stored queries.
     *   <li>When the extending query uses expressions and expression evaluation is deactivated for adhoc queries.
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     *  <li>When a maximum results limit is specified. A maximum results limit can be specified with
     *  the process engine configuration property <code>queryMaxResultsLimit</code> (default
     *  {@link Integer#MAX_VALUE}).
     *  Please use {@link #listPage(String, Query, int, int)} instead.
     */
    public function list(string $filterId, ?QueryInterface $extendingQuery = null): array;

    /**
     * Executes the extended query of a filter and returns the result in the given boundaries as list.
     *
     * @param extendingQuery additional query to extend the filter query
     * @param filterId the id of the filter
     * @param firstResult first result to select
     * @param maxResults maximal number of results
     * @return the query result as list
     * @throws AuthorizationException if the user has no {@link Permissions#READ} permissions on {@link Resources#FILTER}.
     * @throws BadUserRequestException
     *  <ul><li>When the filter query uses expressions and expression evaluation is deactivated for stored queries.
     *  <li>When the extending query uses expressions and expression evaluation is deactivated for adhoc queries.
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     *  <li>When {@param maxResults} exceeds the maximum results limit. A maximum results limit can
     *  be specified with the process engine configuration property <code>queryMaxResultsLimit</code>
     *  (default {@link Integer#MAX_VALUE}).
     */
    public function listPage(string $filterId, int $firstResult, int $maxResults, ?QueryInterface $extendingQuery = null): array;

    /**
     * Executes the extended query of the filter and returns the a single result.
     *
     * @param filterId the the id of the filter
     * @param extendingQuery additional query to extend the filter query
     * @return the single query result
     * @throws AuthorizationException if the user has no {@link Permissions#READ} permissions on {@link Resources#FILTER}.
     * @throws BadUserRequestException
     *  <ul><li>When the filter query uses expressions and expression evaluation is deactivated for stored queries.
     *  <li>When the extending query uses expressions and expression evaluation is deactivated for adhoc queries.
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function singleResult(string $filterId, ?QueryInterface $extendingQuery = null);

    /**
     * Executes the extended query of the filter and returns the result count.
     *
     * @param filterId the the id of the filter
     * @param extendingQuery additional query to extend the filter query
     * @return the result count
     * @throws AuthorizationException if the user has no {@link Permissions#READ} permissions on {@link Resources#FILTER}.
     * @throws BadUserRequestException
     *  <ul><li>When the filter query uses expressions and expression evaluation is deactivated for stored queries.
     *  <li>When the extending query uses expressions and expression evaluation is deactivated for adhoc queries.
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function count(string $filterId, ?QueryInterface $extendingQuery = null): int;
}
