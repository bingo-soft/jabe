<?php

namespace Jabe\Engine\Authorization;

use Jabe\Engine\Query\QueryInterface;

interface AuthorizationQuery extends QueryInterface
{
    /** only selects authorizations for the given id */
    public function authorizationId(string $id): AuthorizationQuery;

    /** only selects authorizations for the given type. */
    public function authorizationType(int $type): AuthorizationQuery;

    /** only selects authorizations for the given user ids */
    public function userIdIn(array $userIds): AuthorizationQuery;

    /** only selects authorizations for the given group ids */
    public function groupIdIn(array $groupIds): AuthorizationQuery;

    /** only selects authorizations for the given resource type */
    public function resourceType(ResourceInterface $resource): AuthorizationQuery;

    /** only selects authorizations for the given resource id */
    public function resourceId(string $resourceId): AuthorizationQuery;

    /** only selects authorizations which grant the permissions represented by the parameter.
     * If this method is called multiple times, all passed-in permissions will be checked with AND semantics.
     * Example:
     *
     * <pre>
     * authorizationQuery.userId("user1")
     *   .resourceType("processDefinition")
     *   .resourceId("2313")
     *   .hasPermission(Permissions.READ)
     *   .hasPermission(Permissions.UPDATE)
     *   .hasPermission(Permissions.DELETE)
     *   .list();
     * </pre>
     *
     * Selects all Authorization objects which provide READ,UPDATE,DELETE
     * Permissions for the given user.
     *
     */
    public function hasPermission(PermissionInterface $permission): AuthorizationQuery;

    // order by /////////////////////////////////////////////

    /** Order by resource type */
    public function orderByResourceType(): AuthorizationQuery;

    /** Order by resource id */
    public function orderByResourceId(): AuthorizationQuery;
}
