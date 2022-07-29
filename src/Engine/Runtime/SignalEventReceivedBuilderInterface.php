<?php

namespace Jabe\Engine\Runtime;

interface SignalEventReceivedBuilderInterface
{
    /**
     * Add the given variables to the triggered executions.
     *
     * @param variables
     *          a map of variables added to the executions
     * @return SignalEventReceivedBuilderInterface the builder
     */
    public function setVariables(array $variables): SignalEventReceivedBuilderInterface;

    /**
     * Specify a single execution to deliver the signal to.
     *
     * @param executionId
     *          the id of the process instance or the execution to deliver the
     *          signal to
     * @return SignalEventReceivedBuilderInterface the builder
     */
    public function executionId(string $executionId): SignalEventReceivedBuilderInterface;

    /**
     * Specify a tenant to deliver the signal to. The signal can only be received
     * on executions or process definitions which belongs to the given tenant.
     * Cannot be used in combination with {@link #executionId(String)}.
     *
     * @param tenantId
     *          the id of the tenant
     * @return SignalEventReceivedBuilderInterface the builder
     */
    public function tenantId(string $tenantId): SignalEventReceivedBuilderInterface;

    /**
     * Specify that the signal can only be received on executions or process
     * definitions which belongs to no tenant. Cannot be used in combination with
     * {@link #executionId(String)}.
     *
     * @return SignalEventReceivedBuilderInterface the builder
     */
    public function withoutTenantId(): SignalEventReceivedBuilderInterface;

    /**
     * <p>
     * Delivers the signal to waiting executions and process definitions. The notification and instantiation happen
     * synchronously.
     * </p>
     *
     * <p>
     * Note that the signal delivers to all tenants if no tenant is specified
     * using {@link #tenantId(String)} or {@link #withoutTenantId()}.
     * </p>
     *
     * @throws ProcessEngineException
     *           if a single execution is specified and no such execution exists
     *           or has not subscribed to the signal
     * @throws AuthorizationException
     *           <li>if notify an execution and the user has no
     *           Permissions#UPDATE permission on
     *           Resources#PROCESS_INSTANCE or no
     *           Permissions#UPDATE_INSTANCE permission on
     *           Resources#PROCESS_DEFINITION.</li>
     *           <li>if start a new process instance and the user has no
     *           Permissions#CREATE permission on
     *           Resources#PROCESS_INSTANCE and no
     *           Permissions#CREATE_INSTANCE permission on
     *           Resources#PROCESS_DEFINITION.</li>
     */
    public function send(): void;
}
