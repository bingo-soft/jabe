<?php

namespace Jabe\Engine\Management;

interface UpdateJobDefinitionSuspensionStateBuilderInterface
{
    /**
     * Specify if the suspension states of the jobs of the provided job
     * definitions should also be updated. Default is <code>false</code>.
     *
     * @param includeJobs
     *          if <code>true</code>, all related jobs will be activated /
     *          suspended too.
     * @return the builder
     */
    public function includeJobs(bool $includeJobs): UpdateJobDefinitionSuspensionStateBuilderInterface;

    /**
     * Specify when the suspension state should be updated. Note that the <b>job
     * executor</b> needs to be active to use this.
     *
     * @param executionDate
     *          the date on which the job definition will be activated /
     *          suspended. If <code>null</code>, the job definition is activated /
     *          suspended immediately.
     *
     * @return the builder
     */
    public function executionDate(string $executionDate): UpdateJobDefinitionSuspensionStateBuilderInterface;

    /**
     * Activates the provided job definitions.
     *
     * @throws AuthorizationException
     *           <li>if the current user has no {@link Permissions#UPDATE}
     *           permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           <li>If {@link #includeJobs(boolean)} is set to <code>true</code>
     *           and the user have no {@link Permissions#UPDATE_INSTANCE}
     *           permission on {@link Resources#PROCESS_DEFINITION}
     *           {@link Permissions#UPDATE} permission on any
     *           {@link Resources#PROCESS_INSTANCE}</li>
     */
    public function activate(): void;

    /**
     * Suspends the provided job definitions. If a job definition is in state
     * suspended, it will be ignored by the job executor.
     *
     * @throws AuthorizationException
     *           <li>if the current user has no {@link Permissions#UPDATE}
     *           permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           <li>If {@link #includeJobs(boolean)} is set to <code>true</code>
     *           and the user have no {@link Permissions#UPDATE_INSTANCE}
     *           permission on {@link Resources#PROCESS_DEFINITION}
     *           {@link Permissions#UPDATE} permission on any
     *           {@link Resources#PROCESS_INSTANCE}</li>
     */
    public function suspend(): void;
}
