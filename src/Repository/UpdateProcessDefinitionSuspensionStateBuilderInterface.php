<?php

namespace Jabe\Repository;

interface UpdateProcessDefinitionSuspensionStateBuilderInterface
{
    /**
     * Specify if the suspension states of the process instances of the provided
     * process definitions should also be updated. Default is <code>false</code>.
     *
     * @param includeProcessInstances
     *          if <code>true</code>, all related process instances will be
     *          activated / suspended too.
     * @return UpdateProcessDefinitionSuspensionStateBuilderInterface the builder
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
     * @return UpdateProcessDefinitionSuspensionStateBuilderInterface the builder
     */
    public function executionDate(?string $executionDate): UpdateProcessDefinitionSuspensionStateBuilderInterface;

    /**
     * Activates the provided process definitions.
     *
     * @throws ProcessEngineException
     *           If no such processDefinition can be found.
     * @throws AuthorizationException
     *           <li>if the user has none of the following:</li>
     *           <ul>
     *           <li>ProcessDefinitionPermissions#SUSPEND permission on Resources#PROCESS_DEFINITION</li>
     *           <li>Permissions#UPDATE permission on Resources#PROCESS_DEFINITION</li>
     *           </ul>
     *           <li>if {@link #includeProcessInstances(boolean)} is set to <code>true</code> and the user has none of the following:</li>
     *           <ul>
     *           <li>ProcessInstancePermissions#SUSPEND permission on Resources#PROCESS_INSTANCE</li>
     *           <li>ProcessDefinitionPermissions#SUSPEND_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *           <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *           <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
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
     *           <li>ProcessDefinitionPermissions#SUSPEND permission on Resources#PROCESS_DEFINITION</li>
     *           <li>Permissions#UPDATE permission on Resources#PROCESS_DEFINITION</li>
     *           </ul>
     *           <li>if {@link #includeProcessInstances(boolean)} is set to <code>true</code> and the user has none of the following:</li>
     *           <ul>
     *           <li>ProcessInstancePermissions#SUSPEND permission on Resources#PROCESS_INSTANCE</li>
     *           <li>ProcessDefinitionPermissions#SUSPEND_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *           <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *           <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *           </ul>
     */
    public function suspend(): void;
}
