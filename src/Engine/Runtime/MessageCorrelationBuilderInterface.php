<?php

namespace BpmPlatform\Engine\Runtime;

interface MessageCorrelationBuilderInterface
{
    /**
     * <p>
     * Correlate the message such that the process instance has a business key with
     * the given name. If the message is correlated to a message start
     * event then the given business key is set on the created process instance.
     * </p>
     *
     * @param businessKey
     *          the businessKey to correlate on.
     * @return the builder
     */
    public function processInstanceBusinessKey(string $businessKey): MessageCorrelationBuilder;

    /**
     * <p>Correlate the message such that the process instance has a
     * variable with the given name and value.</p>
     *
     * @param variableName the name of the process instance variable to correlate on.
     * @param variableValue the value of the process instance variable to correlate on.
     * @return the builder
     */
    public function processInstanceVariableEquals(string $variableName, $variableValue): MessageCorrelationBuilder;

    /**
     * <p>
     * Correlate the message such that the process instance has the given variables.
     * </p>
     *
     * @param variables the variables of the process instance to correlate on.
     * @return the builder
     */
    public function processInstanceVariablesEqual(array $variables): MessageCorrelationBuilder;

    /**
     * <p>Correlate the message such that the execution has a local variable with the given name and value.</p>
     *
     * @param variableName the name of the local variable to correlate on.
     * @param variableValue the value of the local variable to correlate on.
     * @return the builder
     */
    public function localVariableEquals(string $variableName, $variableValue): MessageCorrelationBuilder;
    /**
     * <p>Correlate the message such that the execution has the given variables as local variables.
     * </p>
     *
     * @param variables the local variables of the execution to correlate on.
     * @return the builder
     */
    public function localVariablesEqual(array $variables): MessageCorrelationBuilder;

    /**
     * <p>Correlate the message such that a process instance with the given id is selected.</p>
     *
     * @param id the id of the process instance to correlate on.
     * @return the builder
     */
    public function processInstanceId(string $id): MessageCorrelationBuilder;

    /**
     * <p>Correlate the message such that a process definition with the given id is selected.
     * Is only supported for {@link #correlateStartMessage()} or {@link #startMessageOnly()} flag.</p>
     *
     * @param processDefinitionId the id of the process definition to correlate on.
     * @return the builder
     */
    public function processDefinitionId(string $processDefinitionId): MessageCorrelationBuilder;

    /**
     * <p>Pass a variable to the execution waiting on the message. Use this method for passing the
     * message's payload.</p>
     *
     * <p>Invoking this method multiple times allows passing multiple variables.</p>
     *
     * @param variableName the name of the variable to set
     * @param variableValue the value of the variable to set
     * @return the builder
     */
    public function setVariable(string $variableName, $variableValue): MessageCorrelationBuilder;

    /**
     * <p>Pass a local variable to the execution waiting on the message. Use this method for passing the
     * message's payload.</p>
     *
     * <p>Invoking this method multiple times allows passing multiple variables.</p>
     *
     * @param variableName the name of the variable to set
     * @param variableValue the value of the variable to set
     * @return the builder
     */
    public function setVariableLocal(string $variableName, $variableValue): MessageCorrelationBuilder;

    /**
     * <p>Pass a map of variables to the execution waiting on the message. Use this method
     * for passing the message's payload</p>
     *
     * @param variables the map of variables
     * @return the builder
     */
    public function setVariables(array $variables): MessageCorrelationBuilder;

    /**
     * <p>Pass a map of local variables to the execution waiting on the message. Use this method
     * for passing the message's payload</p>
     *
     * @param variables the map of local variables
     * @return the builder
     */
    public function setVariablesLocal(array $variables): MessageCorrelationBuilder;

    /**
     * Specify a tenant to deliver the message to. The message can only be
     * received on executions or process definitions which belongs to the given
     * tenant. Cannot be used in combination with
     * {@link #processInstanceId(String)} or {@link #processDefinitionId(String)}.
     *
     * @param tenantId
     *          the id of the tenant
     * @return the builder
     */
    public function tenantId(string $tenantId): MessageCorrelationBuilder;

    /**
     * Specify that the message can only be received on executions or process
     * definitions which belongs to no tenant. Cannot be used in combination with
     * {@link #processInstanceId(String)} or {@link #processDefinitionId(String)}.
     *
     * @return the builder
     */
    public function withoutTenantId(): MessageCorrelationBuilder;

    /**
     * Specify that only start message can be correlated.
     *
     * @return the builder
     */
    public function startMessageOnly(): MessageCorrelationBuilder;

    /**
     * Executes the message correlation.
     *
     * @see {@link #correlateWithResult()}
     */
    public function correlate(): void;


    /**
     * Executes the message correlation and returns a {@link MessageCorrelationResult} object.
     *
     * <p>The call of this method will result in either:
     * <ul>
     * <li>Exactly one waiting execution is notified to continue. The notification is performed synchronously. The result contains the execution id.</li>
     * <li>Exactly one Process Instance is started in case the message name matches a message start event of a
     *     process. The instantiation is performed synchronously. The result contains the start event activity id and process definition.</li>
     * <li>MismatchingMessageCorrelationException is thrown. This means that either too many executions / process definitions match the
     *     correlation or that no execution and process definition matches the correlation.</li>
     * </ul>
     * </p>
     * The result can be identified by calling the {@link MessageCorrelationResult#getResultType}.
     *
     * @throws MismatchingMessageCorrelationException
     *          if none or more than one execution or process definition is matched by the correlation
     * @throws AuthorizationException
     *          <li>if one execution is matched and the user has no {@link Permissions#UPDATE} permission on
     *          {@link Resources#PROCESS_INSTANCE} or no {@link Permissions#UPDATE_INSTANCE} permission on
     *          {@link Resources#PROCESS_DEFINITION}.</li>
     *          <li>if one process definition is matched and the user has no {@link Permissions#CREATE} permission on
     *          {@link Resources#PROCESS_INSTANCE} and no {@link Permissions#CREATE_INSTANCE} permission on
     *          {@link Resources#PROCESS_DEFINITION}.</li>
     *
     * @return The result of the message correlation. Result contains either the execution id or the start event activity id and the process definition.
     */
    public function correlateWithResult(): MessageCorrelationResultInterface;

    /**
     * Executes the message correlation. If you do not need access to the process variables, use {@link #correlateWithResult()}
     * to avoid unnecessary variable access.
     *
     * @see {@link #correlateWithResult()}
     *
     * @param deserializeValues if false, returned {@link SerializableValue}s
     *   will not be deserialized (unless they are passed into this method as a
     *   deserialized value or if the BPMN process triggers deserialization)
     *
     * @return The result of the message correlation. Result contains either the
     *         execution id or the start event activity id, the process definition,
     *         and the process variables.
     */
    public function correlateWithResultAndVariables(bool $deserializeValues): MessageCorrelationResultWithVariablesInterface;

    /**
     * <p>
     *   Behaves like {@link #correlate()}, however uses pessimistic locking for correlating a waiting execution, meaning
     *   that two threads correlating a message to the same execution in parallel do not end up continuing the
     *   process in parallel until the next wait state is reached
     * </p>
     * <p>
     *   <strong>CAUTION:</strong> Wherever there are pessimistic locks, there is a potential for deadlocks to occur.
     *   This can either happen when multiple messages are correlated in parallel, but also with other
     *   race conditions such as a message boundary event on a user task. The process engine is not able to detect such a potential.
     *   In consequence, the user of this API should investigate this potential in his/her use case and implement
     *   countermeasures if needed.
     * </p>
     * <p>
     *   A less error-prone alternative to this method is to set appropriate async boundaries in the process model
     *   such that parallel message correlation is solved by optimistic locking.
     * </p>
     */
    public function correlateExclusively(): void;


    /**
     * Executes the message correlation for multiple messages.
     *
     * @see {@link #correlateAllWithResult()}
     */
    public function correlateAll(): void;

    /**
     * Executes the message correlation for multiple messages and returns a list of message correlation results.
     *
     * <p>This will result in any number of the following:
     * <ul>
     * <li>Any number of waiting executions are notified to continue. The notification is performed synchronously. The result list contains the execution ids of the
     * notified executions.</li>
     * <li>Any number of process instances are started which have a message start event that matches the message name. The instantiation is performed synchronously.
     * The result list contains the start event activity ids and process definitions from all activities on that the messages was correlated to.</li>
     * </ul>
     * </p>
     * <p>Note that the message correlates to all tenants if no tenant is specified using {@link #tenantId(String)} or {@link #withoutTenantId()}.</p>
     *
     * @throws AuthorizationException
     *          <li>if at least one execution is matched and the user has no {@link Permissions#UPDATE} permission on
     *          {@link Resources#PROCESS_INSTANCE} or no {@link Permissions#UPDATE_INSTANCE} permission on
     *          {@link Resources#PROCESS_DEFINITION}.</li>
     *          <li>if one process definition is matched and the user has no {@link Permissions#CREATE} permission on
     *          {@link Resources#PROCESS_INSTANCE} and no {@link Permissions#CREATE_INSTANCE} permission on
     *          {@link Resources#PROCESS_DEFINITION}.</li>
     *
     * @return The result list of the message correlations. Each result contains
     * either the execution id or the start event activity id and the process definition.
     */
    public function correlateAllWithResult(): array;

    /**
     * Executes the message correlation. If you do not need access to the process variables, use {@link #correlateAllWithResult()}
     * to avoid unnecessary variable access.
     *
     * @see {@link #correlateAllWithResult()}
     *
     * @param deserializeValues if false, returned {@link SerializableValue}s
     *   will not be deserialized (unless they are passed into this method as a
     *   deserialized value or if the BPMN process triggers deserialization)
     *
     * @return The result list of the message correlations. Each result contains
     *         either the execution id or the start event activity id, the process
     *         definition, and the process variables.
     */
    public function correlateAllWithResultAndVariables(bool $deserializeValues): array;

    /**
     * Executes the message correlation.
     *
     * <p>
     * This will result in either:
     * <ul>
     * <li>Exactly one Process Instance is started in case the message name
     * matches a message start event of a process. The instantiation is performed
     * synchronously.</li>
     * <li>MismatchingMessageCorrelationException is thrown. This means that
     * either no process definition or more than one process definition matches
     * the correlation.</li>
     * </ul>
     * </p>
     *
     * @return the newly created process instance
     *
     * @throws MismatchingMessageCorrelationException
     *           if none or more than one process definition is matched by the correlation
     * @throws AuthorizationException
     *           if one process definition is matched and the user has no
     *           {@link Permissions#CREATE} permission on
     *           {@link Resources#PROCESS_INSTANCE} and no
     *           {@link Permissions#CREATE_INSTANCE} permission on
     *           {@link Resources#PROCESS_DEFINITION}.
     */
    public function correlateStartMessage(): ProcessInstanceInterface;
}
