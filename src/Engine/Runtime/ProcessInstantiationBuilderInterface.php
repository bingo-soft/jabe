<?php

namespace Jabe\Engine\Runtime;

interface ProcessInstantiationBuilderInterface extends ActivityInstantiationBuilderInterface, InstantiationBuilderInterface
{
    /**
     * Specify the id of the tenant the process definition belongs to. Can only be
     * used when the definition is referenced by <code>key</code> and not by <code>id</code>.
     */
    public function processDefinitionTenantId(string $tenantId): ProcessInstantiationBuilderInterface;

    /**
     * Specify that the process definition belongs to no tenant. Can only be
     * used when the definition is referenced by <code>key</code> and not by <code>id</code>.
     */
    public function processDefinitionWithoutTenantId(): ProcessInstantiationBuilderInterface;

    /**
     * Set the business key for the process instance
     */
    public function businessKey(string $businessKey): ProcessInstantiationBuilderInterface;

    /**
     * Associate a case instance with the process instance
     */
    public function caseInstanceId(string $caseInstanceId): ProcessInstantiationBuilderInterface;

    /**
     * Start the process instance.
     *
     * @param skipCustomListeners
     *          specifies whether custom listeners (task and execution) should be
     *          invoked when executing the instructions. Only supported for
     *          instructions.
     * @param skipIoMappings
     *          specifies whether input/output mappings for tasks should be
     *          invoked throughout the transaction when executing the
     *          instructions. Only supported for instructions.
     * @return the newly created process instance
     * @see also {@link #executeWithVariablesInReturn(boolean, boolean)}.
     */
    public function execute(bool $skipCustomListeners, bool $skipIoMappings): ProcessInstanceInterface;

    /**
     * Start the process instance. If no instantiation instructions are set then
     * the instance start at the default start activity. Otherwise, all
     * instructions are executed in the order they are submitted.
     *
     * @param skipCustomListeners
     *          specifies whether custom listeners (task and execution) should be
     *          invoked when executing the instructions. Only supported for
     *          instructions.
     * @param skipIoMappings
     *          specifies whether input/output mappings for tasks should be
     *          invoked throughout the transaction when executing the
     *          instructions. Only supported for instructions.
     * @return the newly created process instance with the latest variables
     *
     * @throws AuthorizationException
     *           if the user has no {@link Permissions#CREATE} permission on
     *           {@link Resources#PROCESS_INSTANCE} and no
     *           {@link Permissions#CREATE_INSTANCE} permission on
     *           {@link Resources#PROCESS_DEFINITION}.
     *
     * @throws ProcessEngineException
     *           if {@code skipCustomListeners} or {@code skipIoMappings} is set
     *           to true but no instructions are submitted. Both options are not
     *           supported when the instance starts at the default start activity.
     *           Use {@link #execute()} instead.
     *
     */
    public function executeWithVariablesInReturn(bool $skipCustomListeners, bool $skipIoMappings): ProcessInstanceWithVariablesInterface;
}
