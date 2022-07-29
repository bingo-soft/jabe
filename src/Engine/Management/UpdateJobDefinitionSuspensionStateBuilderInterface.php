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
     * @return UpdateJobDefinitionSuspensionStateBuilderInterface the builder
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
     * @return UpdateJobDefinitionSuspensionStateBuilderInterface the builder
     */
    public function executionDate(string $executionDate): UpdateJobDefinitionSuspensionStateBuilderInterface;

    /**
     * Activates the provided job definitions.
     *
     * @throws AuthorizationException
     *           <li>if the current user has no Permissions#UPDATE
     *           permission on Resources#PROCESS_DEFINITION</li>
     *           <li>If {@link #includeJobs(boolean)} is set to <code>true</code>
     *           and the user have no Permissions#UPDATE_INSTANCE
     *           permission on Resources#PROCESS_DEFINITION
     *           Permissions#UPDATE permission on any
     *           Resources#PROCESS_INSTANCE</li>
     */
    public function activate(): void;

    /**
     * Suspends the provided job definitions. If a job definition is in state
     * suspended, it will be ignored by the job executor.
     *
     * @throws AuthorizationException
     *           <li>if the current user has no Permissions#UPDATE
     *           permission on Resources#PROCESS_DEFINITION</li>
     *           <li>If {@link #includeJobs(boolean)} is set to <code>true</code>
     *           and the user have no Permissions#UPDATE_INSTANCE
     *           permission on Resources#PROCESS_DEFINITION
     *           Permissions#UPDATE permission on any
     *           Resources#PROCESS_INSTANCE</li>
     */
    public function suspend(): void;
}
