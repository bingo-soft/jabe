<?php

namespace Jabe\Engine\Repository;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\ProcessDefinitionQueryImpl;
use Jabe\Engine\Query\QueryInterface;

interface ProcessDefinitionQueryInterface extends QueryInterface
{
    /** Only select process definiton with the given id.  */
    public function processDefinitionId(string $processDefinitionId): ProcessDefinitionQueryInterface;

    /** Only select process definiton with the given id.  */
    public function processDefinitionIdIn(array $ids): ProcessDefinitionQueryInterface;

    /** Only select process definitions with the given category. */
    public function processDefinitionCategory(string $processDefinitionCategory): ProcessDefinitionQueryInterface;

    /**
     * Only select process definitions where the category matches the given parameter.
     * The syntax that should be used is the same as in SQL, eg. %activiti%
     */
    public function processDefinitionCategoryLike(string $processDefinitionCategoryLike): ProcessDefinitionQueryInterface;

    /** Only select process definitions with the given name. */
    public function processDefinitionName(string $processDefinitionName): ProcessDefinitionQueryInterface;

    /**
     * Only select process definitions where the name matches the given parameter.
     * The syntax that should be used is the same as in SQL, eg. %activiti%
     */
    public function processDefinitionNameLike(string $processDefinitionNameLike): ProcessDefinitionQueryInterface;

    /**
     * Only select process definitions that are deployed in a deployment with the
     * given deployment id
     */
    public function deploymentId(string $deploymentId): ProcessDefinitionQueryInterface;

    /**
     * Only select process definitions that were deployed after the given Date (exclusive).
     */
    public function deployedAfter(string $deployedAfter): ProcessDefinitionQueryInterface;

    /**
     * Only select process definitions that were deployed at the given Date.
     */
    public function deployedAt(string $deployedAt): ProcessDefinitionQueryInterface;

    /**
     * Only select process definition with the given key.
     */
    public function processDefinitionKey(string $processDefinitionKey): ProcessDefinitionQueryInterface;

    /**
     * Only select process definitions with the given keys
     */
    public function processDefinitionKeysIn(array $processDefinitionKeys): ProcessDefinitionQueryImpl;

    /**
     * Only select process definitions where the key matches the given parameter.
     * The syntax that should be used is the same as in SQL, eg. %activiti%
     */
    public function processDefinitionKeyLike(string $processDefinitionKeyLike): ProcessDefinitionQueryInterface;

    /**
     * Only select process definition with a certain version.
     * Particulary useful when used in combination with {@link #processDefinitionKey(String)}
     */
    public function processDefinitionVersion(int $processDefinitionVersion): ProcessDefinitionQueryInterface;

    /**
     * <p>
     * Only select the process definitions which are the latest deployed (ie.
     * which have the highest version number for the given key).
     * </p>
     *
     * <p>
     * Can only be used in combination with {@link #processDefinitionKey(String)}
     * of {@link #processDefinitionKeyLike(String)}. Can also be used without any
     * other criteria (ie. query.latest().list()), which will then give all the
     * latest versions of all the deployed process definitions.
     * </p>
     *
     * <p>For multi-tenancy: select the latest deployed process definitions for each
     * tenant. If a process definition is deployed for multiple tenants then all
     * process definitions are selected.</p>
     *
     * @throws ProcessEngineException
     *           if used in combination with {@link #groupId(string)},
     *           {@link #processDefinitionVersion(int)} or
     *           {@link #deploymentId(String)}
     */
    public function latestVersion(): ProcessDefinitionQueryInterface;

    /** Only select process definition with the given resource name. */
    public function processDefinitionResourceName(string $resourceName): ProcessDefinitionQueryInterface;

    /** Only select process definition with a resource name like the given . */
    public function processDefinitionResourceNameLike(string $resourceNameLike): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions which given userId is authorized to start
     */
    public function startableByUser(string $userId): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions which are suspended
     */
    public function suspended(): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions which are active
     */
    public function active(): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions with the given incident type.
     */
    public function incidentType(string $incidentType): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions with the given incident id.
     */
    public function incidentId(string $incidentId): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions with the given incident message.
     */
    public function incidentMessage(string $incidentMessage): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions with an incident message like the given.
     */
    public function incidentMessageLike(string $incidentMessageLike): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions with a specific version tag
     */
    public function versionTag(string $versionTag): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions with a version tag like the given
     */
    public function versionTagLike(string $versionTagLike): ProcessDefinitionQueryInterface;

    /**
     * Only selects process definitions without a version tag
     */
    public function withoutVersionTag(): ProcessDefinitionQueryInterface;

    /**
     * Selects the single process definition which has a start message event
     * with the messageName.
     */
    public function messageEventSubscriptionName(string $messageName): ProcessDefinitionQueryInterface;

    /** Only select process definitions with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): ProcessDefinitionQueryInterface;

    /** Only select process definitions which have no tenant id. */
    public function withoutTenantId(): ProcessDefinitionQueryInterface;

    /**
     * Select process definitions which have no tenant id. Can be used in
     * combination with {@link #tenantIdIn(String...)}.
     */
    public function includeProcessDefinitionsWithoutTenantId(): ProcessDefinitionQueryInterface;

    /**
     * Select process definitions which could be started in Tasklist.
     */
    public function startableInTasklist(): ProcessDefinitionQueryInterface;

    /**
     * Select process definitions which could not be started in Tasklist.
     */
    public function notStartableInTasklist(): ProcessDefinitionQueryInterface;

    public function startablePermissionCheck(): ProcessDefinitionQueryInterface;

    // ordering ////////////////////////////////////////////////////////////

    /** Order by the category of the process definitions (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionCategory(): ProcessDefinitionQueryInterface;

    /** Order by process definition key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionKey(): ProcessDefinitionQueryInterface;

    /** Order by the id of the process definitions (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): ProcessDefinitionQueryInterface;

    /** Order by the version of the process definitions (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionVersion(): ProcessDefinitionQueryInterface;

    /** Order by the name of the process definitions (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionName(): ProcessDefinitionQueryInterface;

    /** Order by deployment id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByDeploymentId(): ProcessDefinitionQueryInterface;

    /** Order by deployment time (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByDeploymentTime(): ProcessDefinitionQueryInterface;

    /** Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of process instances without tenant id is database-specific. */
    public function orderByTenantId(): ProcessDefinitionQueryInterface;

    /**
     * Order by version tag (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * <strong>Note:</strong> sorting by versionTag is a string $based sort.
     * There is no interpretation of the version which can lead to a sorting like:
     * v0.1.0 v0.10.0 v0.2.0.
     */
    public function orderByVersionTag(): ProcessDefinitionQueryInterface;
}
