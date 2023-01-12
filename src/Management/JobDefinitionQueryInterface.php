<?php

namespace Jabe\Management;

use Jabe\Query\QueryInterface;

interface JobDefinitionQueryInterface extends QueryInterface
{
    /** Only select job definitions with the given id */
    public function jobDefinitionId(?string $jobDefinitionId): JobDefinitionQueryInterface;

    /** Only select job definitions which exist for the listed activity ids */
    public function activityIdIn(array $activityIds): JobDefinitionQueryInterface;

    /** Only select job definitions which exist for the given process definition id. **/
    public function processDefinitionId(?string $processDefinitionId): JobDefinitionQueryInterface;

    /** Only select job definitions which exist for the given process definition key. **/
    public function processDefinitionKey(?string $processDefinitionKey): JobDefinitionQueryInterface;

    /** Only select job definitions which have the given job type. **/
    public function jobType(?string $jobType): JobDefinitionQueryInterface;

    /** Only select job definitions which contain the configuration. **/
    public function jobConfiguration(?string $jobConfiguration): JobDefinitionQueryInterface;

    /** Only selects job definitions which are active **/
    public function active(): JobDefinitionQueryInterface;

    /** Only selects job definitions which are suspended **/
    public function suspended(): JobDefinitionQueryInterface;

    /**
     * Only selects job definitions which have a job priority defined.
     *
     * @since 7.4
     */
    public function withOverridingJobPriority(): JobDefinitionQueryInterface;

    /** Only select job definitions that belong to one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): JobDefinitionQueryInterface;

    /** Only select job definitions which have no tenant id. */
    public function withoutTenantId(): JobDefinitionQueryInterface;

    /**
     * Select job definitions which have no tenant id. Can be used in combination
     * with {@link #tenantIdIn(String...)}.
     */
    public function includeJobDefinitionsWithoutTenantId(): JobDefinitionQueryInterface;

    /** Order by id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByJobDefinitionId(): JobDefinitionQueryInterface;

    /** Order by activty id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityId(): JobDefinitionQueryInterface;

    /** Order by process defintion id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): JobDefinitionQueryInterface;

    /** Order by process definition key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionKey(): JobDefinitionQueryInterface;

    /** Order by job type (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByJobType(): JobDefinitionQueryInterface;

    /** Order by job configuration (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByJobConfiguration(): JobDefinitionQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of job definitions without tenant id is database-specific.
     */
    public function orderByTenantId(): JobDefinitionQueryInterface;
}
