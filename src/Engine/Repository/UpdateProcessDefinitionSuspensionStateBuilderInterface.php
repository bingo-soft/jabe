<?php

namespace BpmPlatform\Engine\Repository;

interface UpdateProcessDefinitionSuspensionStateBuilderInterface
{
    /**
     * Specify if the suspension states of the process instances of the provided
     * process definitions should also be updated. Default is <code>false</code>.
     *
     * @param includeProcessInstances
     *          if <code>true</code>, all related process instances will be
     *          activated / suspended too.
     * @return the builder
     */
    public function includeProcessInstances(bool $includeProcessInstances): UpdateProcessDefinitionSuspensionStateBuilderInterface;

    /**
     * Specify when the suspension state should be updated. Note that the <b>job
     * executor</b> needs to be active to use this.
     *
     * @param executionDate
     *          the date on which the process definition will be activated /
     *          suspended. If <code>null</code>, the process definition is
     *          activated / suspended immediately.
     *
     * @return the builder
     */
    public function executionDate(string $executionDate): UpdateProcessDefinitionSuspensionStateBuilderInterface;

    /**
     * Activates the provided process definitions.
     *
     * @throws ProcessEngineException
     *           If no such processDefinition can be found.
     * @throws AuthorizationException
     *           <li>if the user has none of the following:</li>
     *           <ul>
     *           <li>{@link ProcessDefinitionPermissions#SUSPEND} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           <li>{@link Permissions#UPDATE} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           </ul>
     *           <li>if {@link #includeProcessInstances(boolean)} is set to <code>true</code> and the user has none of the following:</li>
     *           <ul>
     *           <li>{@link ProcessInstancePermissions#SUSPEND} permission on {@link Resources#PROCESS_INSTANCE}</li>
     *           <li>{@link ProcessDefinitionPermissions#SUSPEND_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           <li>{@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}</li>
     *           <li>{@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           </ul>
     */
    public function activate(): void;

    /**
     * Suspends the provided process definitions. If a process definition is in
     * state suspended, it will not be possible to start new process instances
     * based on this process definition.
     *
     * @throws ProcessEngineException
     *           If no such processDefinition can be found.
     * @throws AuthorizationException
     *           <li>if the user has none of the following:</li>
     *           <ul>
     *           <li>{@link ProcessDefinitionPermissions#SUSPEND} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           <li>{@link Permissions#UPDATE} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           </ul>
     *           <li>if {@link #includeProcessInstances(boolean)} is set to <code>true</code> and the user has none of the following:</li>
     *           <ul>
     *           <li>{@link ProcessInstancePermissions#SUSPEND} permission on {@link Resources#PROCESS_INSTANCE}</li>
     *           <li>{@link ProcessDefinitionPermissions#SUSPEND_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           <li>{@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}</li>
     *           <li>{@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *           </ul>
     */
    public function suspend(): void;
}
