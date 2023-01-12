<?php

namespace Jabe\Repository;

use Jabe\Query\QueryInterface;

interface DeploymentQueryInterface extends QueryInterface
{
    /** Only select deployments with the given deployment id. */
    public function deploymentId(?string $deploymentId): DeploymentQueryInterface;

    /** Only select deployments with the given name. */
    public function deploymentName(?string $name): DeploymentQueryInterface;

    /** Only select deployments with a name like the given string. */
    public function deploymentNameLike(?string $nameLike): DeploymentQueryInterface;

    /**
     * If the given <code>source</code> is <code>null</code>,
     * then deployments are returned where source is equal to null.
     * Otherwise only deployments with the given source are
     * selected.
     */
    public function deploymentSource(?string $source): DeploymentQueryInterface;

    /** Only select deployments deployed before the given date */
    public function deploymentBefore(?string $before): DeploymentQueryInterface;

    /** Only select deployments deployed after the given date */
    public function deploymentAfter(?string $after): DeploymentQueryInterface;

    /** Only select deployments with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): DeploymentQueryInterface;

    /** Only select deployments which have no tenant id. */
    public function withoutTenantId(): DeploymentQueryInterface;

    /**
     * Select deployments which have no tenant id. Can be used in
    * combination with {@link #tenantIdIn(String...)}.
    */
    public function includeDeploymentsWithoutTenantId(): DeploymentQueryInterface;

    //sorting ////////////////////////////////////////////////////////

    /** Order by deployment id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByDeploymentId(): DeploymentQueryInterface;

    /** Order by deployment name (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByDeploymentName(): DeploymentQueryInterface;

    /** Order by deployment time (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByDeploymentTime(): DeploymentQueryInterface;

    /** Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of process instances without tenant id is database-specific. */
    public function orderByTenantId(): DeploymentQueryInterface;
}
