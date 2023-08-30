<?php

namespace Jabe;

use Jabe\Authorization\{
    BatchPermissions,
    Permissions,
    ProcessDefinitionPermissions,
    ProcessInstancePermissions,
    Resources
};
use Jabe\Batch\BatchInterface;
use Jabe\Exception\{
    NullValueException,
    NotFoundException,
    NotValidException
};
use Jabe\History\HistoricProcessInstanceQueryInterface;
use Jabe\Migration\{
    MigrationPlanInterface,
    MigrationPlanBuilderInterface,
    MigrationPlanExecutionBuilderInterface
};
use Jabe\Repository\{
    DeploymentInterface,
    ResourceDefinitionInterface
};
use Jabe\Runtime\{
    ActivityInstanceInterface,
    ConditionEvaluationBuilderInterface,
    EventSubscriptionQueryInterface,
    ExecutionInterface,
    ExecutionQueryInterface,
    IncidentInterface,
    IncidentQueryInterface,
    MessageCorrelationBuilderInterface,
    ModificationBuilderInterface,
    NativeExecutionQueryInterface,
    NativeProcessInstanceQueryInterface,
    ProcessInstanceInterface,
    ProcessInstanceModificationBuilderInterface,
    ProcessInstanceQueryInterface,
    ProcessInstantiationBuilderInterface,
    RestartProcessInstanceBuilderInterface,
    SignalEventReceivedBuilderInterface,
    UpdateProcessInstanceSuspensionStateBuilderInterface,
    UpdateProcessInstanceSuspensionStateSelectBuilderInterface,
    VariableInstanceQueryInterface
};
use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Value\{
    SerializableValueInterface,
    TypedValueInterface
};

interface RuntimeServiceInterface
{
    /**
     * Starts a new process instance in the latest version of the process definition with the given key.
     *
     * A business key can be provided to associate the process instance with a
     * certain identifier that has a clear business meaning. For example in an
     * order process, the business key could be an order id. This business key can
     * then be used to easily look up that process instance , see
     * ProcessInstanceQuery#processInstanceBusinessKey(String). Providing such a business
     * key is definitely a best practice.
     *
     * Note that a business key MUST be unique for the given process definition WHEN you have added a
     * database constraint for it.
     * In this case, only Process instance from different process definition are allowed to have the
     * same business key and the combination of processdefinitionKey-businessKey must be unique.
     *
     * The combination of processdefinitionKey-businessKey must be unique.
     *
     * @param processDefinitionKey key of process definition, cannot be null.
     * @param variables the variables to pass, can be null.
     * @param businessKey a key that uniquely identifies the process instance in the context of the
     *                    given process definition.
     *
     * @throws ProcessEngineException
     *          when no process definition is deployed with the given key.
     * @throws AuthorizationException
     *          if the user has no Permissions#CREATE permission on Resources#PROCESS_INSTANCE
     *          and no Permissions#CREATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function startProcessInstanceByKey(?string $processDefinitionKey, /*?string|array*/...$args): ProcessInstanceInterface;

    /**
     * Starts a new process instance in the exactly specified version of the process definition with the given id.
     *
     * A business key can be provided to associate the process instance with a
     * certain identifier that has a clear business meaning. For example in an
     * order process, the business key could be an order id. This business key can
     * then be used to easily look up that process instance , see
     * ProcessInstanceQuery#processInstanceBusinessKey(String). Providing such a business
     * key is definitely a best practice.
     *
     * Note that a business key MUST be unique for the given process definition WHEN you have added
     * a database constraint for it.
     * In this case, only Process instance from different process definition are allowed to have the
     * same business key and the combination of processdefinitionKey-businessKey must be unique.
     *
     * @param processDefinitionId the id of the process definition, cannot be null.
     * @param businessKey a key that uniquely identifies the process instance in the context of the
     *                    given process definition.
     * @param variables variables to be passed, can be null
     *
     * @throws ProcessEngineException
     *          when no process definition is deployed with the given key.
     * @throws AuthorizationException
     *          if the user has no Permissions#CREATE permission on Resources#PROCESS_INSTANCE
     *          and no Permissions#CREATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function startProcessInstanceById(?string $processDefinitionId, ?string $businessKey = null, array $variables = []): ProcessInstanceInterface;

    /**
     * <p>Signals the process engine that a message is received and starts a new
     * ProcessInstance.</p>
     *
     * See {@link #startProcessInstanceByMessage(String, Map)}. In addition, this method allows
     * specifying a business key.
     *
     * @param messageName
     *          the 'name' of the message as specified as an attribute on the
     *          bpmn20 {@code <message name="messageName" />} element.
     * @param businessKey
     *          the business key which is added to the started process instance
     * @param processVariables
     *          the 'payload' of the message. The variables are added as processes
     *          variables to the started process instance.
     *
     * @return ProcessInstanceInterface the ProcessInstance object representing the started process instance
     *
     * @throws ProcessEngineException
     *          if no subscription to a message with the given name exists
     * @throws AuthorizationException
     *          if the user has no Permissions#CREATE permission on Resources#PROCESS_INSTANCE
     *          and no Permissions#CREATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     *
     * @since 5.9
     */
    public function startProcessInstanceByMessage(?string $messageName, ?string $businessKey = null, array $processVariables = []): ProcessInstanceInterface;

    /**
     * <p>Signals the process engine that a message is received and starts a new
     * ProcessInstance.</p>
     *
     * See {@link #startProcessInstanceByMessage(String, String)}. In addition, this method allows
     * specifying the exactly version of the process definition with the given id.
     *
     * @param messageName
     *          the 'name' of the message as specified as an attribute on the
     *          bpmn20 {@code <message name="messageName" />} element, cannot be null.
     * @param processDefinitionId
     *      the id of the process definition, cannot be null.
     * @param businessKey
     *          the business key which is added to the started process instance
     *
     * @return ProcessInstanceInterface the ProcessInstance object representing the started process instance
     *
     * @throws ProcessEngineException
     *          if no subscription to a message with the given name exists for the
     *          specified version of process definition.
     * @throws AuthorizationException
     *          if the user has no Permissions#CREATE permission on Resources#PROCESS_INSTANCE
     *          and no Permissions#CREATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     *
     * @since 7.3
     */
    public function startProcessInstanceByMessageAndProcessDefinitionId(?string $messageName, ?string $processDefinitionId, ?string $businessKey = null, array $processVariables = []): ProcessInstanceInterface;

    /**
     * Delete an existing runtime process instances asynchronously using Batch operation.
     *
     * Deletion propagates upward as far as necessary.
     *
     * @param processInstanceIds id's of process instances to delete.
     * @param processInstanceQuery query that will be used to fetch affected process instances.
     * @param historicProcessInstanceQuery query that will be used to fetch affected
     *                                     process instances based on history data.
     * @param deleteReason reason for deleting, which will be stored in the history. Can be null.
     * @param skipCustomListeners skips custom execution listeners when removing instances
     * @param skipSubprocesses skips subprocesses when removing instances
     *
     * @throws BadUserRequestException
     *          when no process instance is found with the given queries or ids.
     * @throws AuthorizationException
     *          If the user has no Permissions#CREATE or
     *          BatchPermissions#CREATE_BATCH_DELETE_RUNNING_PROCESS_INSTANCES permission on Resources#BATCH.
     */
    public function deleteProcessInstancesAsync(
        array $processInstanceIds,
        ProcessInstanceQueryInterface $processInstanceQuery = null,
        HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery = null,
        ?string $deleteReason = null,
        bool $skipCustomListeners = false,
        bool $skipSubprocesses = false
    ): BatchInterface;

    /**
     * Delete an existing runtime process instance.
     *
     * Deletion propagates upward as far as necessary.
     *
     * @param processInstanceId id of process instance to delete, cannot be null.
     * @param deleteReason reason for deleting, which will be stored in the history. Can be null.
     * @param skipCustomListeners if true, only the built-in ExecutionListeners
     * are notified with the ExecutionListener#EVENTNAME_END event.
     * @param externallyTerminated indicator if deletion triggered from external context, for instance
     *                             REST API call
     *
     *
     * @throws BadUserRequestException
     *          when the processInstanceId is null.
     * @throws NotFoundException
     *          when no process instance is found with the given processInstanceId.
     * @throws AuthorizationException
     *          if the user has no Permissions#DELETE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#DELETE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteProcessInstance(
        ?string $processInstanceId,
        ?string $deleteReason = null,
        bool $skipCustomListeners = false,
        bool $skipIoMappings = false,
        bool $externallyTerminated = false,
        bool $skipSubprocesses = false
    ): void;

    /**
     * Delete existing runtime process instances.
     *
     * Deletion propagates upward as far as necessary.
     *
     * @param processInstanceIds ids of process instance to delete, cannot be null.
     * @param deleteReason reason for deleting, which will be stored in the history. Can be null.
     * @param skipCustomListeners if true, only the built-in ExecutionListeners
     * are notified with the ExecutionListener#EVENTNAME_END event.
     * @param externallyTerminated indicator if deletion triggered from external context, for instance
     *                             REST API call
     * @param skipSubprocesses specifies whether subprocesses should be deleted
     *
     *
     * @throws BadUserRequestException
     *          when a processInstanceId is null.
     * @throws NotFoundException
     *          when no process instance is found with a given processInstanceId.
     * @throws AuthorizationException
     *          if the user has no Permissions#DELETE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#DELETE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteProcessInstances(
        array $processInstanceIds,
        ?string $deleteReason = null,
        bool $skipCustomListeners = false,
        bool $externallyTerminated = false,
        bool $skipSubprocesses = false
    ): void;

    /**
     * Delete existing runtime process instances.
     *
     * Deletion propagates upward as far as necessary.
     *
     * Does not fail if a process instance was not found.
     *
     * @param processInstanceIds ids of process instance to delete, cannot be null.
     * @param deleteReason reason for deleting, which will be stored in the history. Can be null.
     * @param skipCustomListeners if true, only the built-in ExecutionListeners
     * are notified with the ExecutionListener#EVENTNAME_END event.
     * @param externallyTerminated indicator if deletion triggered from external context, for instance
     *                             REST API call
     * @param skipSubprocesses specifies whether subprocesses should be deleted
     *
     *
     * @throws BadUserRequestException
     *          when a processInstanceId is null.
     * @throws AuthorizationException
     *          if the user has no Permissions#DELETE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#DELETE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteProcessInstancesIfExists(array $processInstanceIds, ?string $deleteReason = null, bool $skipCustomListeners = false, bool $externallyTerminated = false, bool $skipSubprocesses = false): void;

    /**
     * Delete an existing runtime process instance.
     *
     * Deletion propagates upward as far as necessary.
     *
     * Does not fail if a process instance was not found.
     *
     * @param processInstanceId id of process instance to delete, cannot be null.
     * @param deleteReason reason for deleting, which will be stored in the history. Can be null.
     * @param skipCustomListeners if true, only the built-in ExecutionListeners
     * are notified with the ExecutionListener#EVENTNAME_END event.
     * @param externallyTerminated indicator if deletion triggered from external context, for instance
     *                             REST API call
     * @param skipIoMappings specifies whether input/output mappings for tasks should be invoked
     * @param skipSubprocesses specifies whether subprocesses should be deleted
     *
     *
     * @throws BadUserRequestException
     *          when processInstanceId is null.
     * @throws AuthorizationException
     *          if the user has no Permissions#DELETE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#DELETE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteProcessInstanceIfExists(?string $processInstanceId, ?string $deleteReason = null, bool $skipCustomListeners = false, bool $externallyTerminated = false, bool $skipIoMappings = false, bool $skipSubprocesses = false): void;

    /**
     * Finds the activity ids for all executions that are waiting in activities.
     * This is a list because a single activity can be active multiple times.
     *
     * Deletion propagates upward as far as necessary.
     *
     * @param executionId id of the process instance or the execution, cannot be null.
     *
     * @throws ProcessEngineException
     *          when no execution exists with the given executionId.
     * @throws AuthorizationException
     *          if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function getActiveActivityIds(?string $executionId): array;

    /**
     * <p>Allows retrieving the activity instance tree for a given process instance.
     * The activity instance tree is aligned with the concept of scope in the BPMN specification.
     * Activities that are "on the same level of subprocess" (ie. part of the same scope, contained
     * in the same subprocess) will have their activity instances at the same level in the tree.</p>
     *
     * <h2>Examples:</h2>
     * <p><ul>
     *  <li>Process with two parallel user tasks after parallel Gateway: in the activity instance tree you
     *  will see two activity instances below the root instance, one for each user task.</li>
     *  <li>Process with two parallel Multi Instance user tasks after parallel Gateway: in the activity instance
     *  tree, all instances of both user tasks will be listed below the root activity instance. Reason: all
     *  activity instances are at the same level of subprocess.</li>
     *  <li>Usertask inside embedded subprocess: the activity instance three will have 3 levels: the root instance
     *  representing the process instance itself, below it an activity instance representing the instance of the embedded
     *  subprocess, and below this one, the activity instance representing the usertask.</li>
     * </ul></p>
     *
     * <h2>Identity & Uniqueness:</h2>
     * <p>Each activity instance is assigned a unique Id. The id is persistent, if you invoke this method multiple times,
     * the same activity instance ids will be returned for the same activity instances. (However, there might be
     * different executions assigned, see below)</p>
     *
     * <h2>Relation to Executions</h2>
     * <p>The Execution concept in the process engine is not completely aligned with the activity
     * instance concept because the execution tree is in general not aligned with the activity / scope concept in
     * BPMN. In general, there is a n-1 relationship between Executions and ActivityInstances, ie. at a given
     * point in time, an activity instance can be linked to multiple executions. In addition, it is not guaranteed
     * that the same execution that started a given activity instance will also end it. The process engine performs
     * several internal optimizations concerning the compacting of the execution tree which might lead to executions
     * being reordered and pruned. This can lead to situations where a given execution starts an activity instance
     * but another execution ends it. Another special case is the process instance: if the process instance is executing
     * a non-scope activity (for example a user task) below the process definition scope, it will be referenced
     * by both the root activity instance and the user task activity instance.
     *
     * <p><strong>If you need to interpret the state of a process instance in terms of a BPMN process model, it is usually easier to
     * use the activity instance tree as opposed to the execution tree.</strong></p>
     *
     * @param processInstanceId the id of the process instance for which the activity instance tree should be constructed.
     *
     * @return ActivityInstanceInterface the activity instance tree for a given process instance or null if no such process instance exists.
     *
     * @throws ProcessEngineException
     *          if processInstanceId is 'null' or an internal error occurs.
     * @throws AuthorizationException
     *          if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION.
     *
     * @since 7.0
     */
    public function getActivityInstance(?string $processInstanceId): ActivityInstanceInterface;

    /**
     * Sends an external trigger to an activity instance that is waiting inside the given execution.
     *
     * Note that you need to provide the exact execution that is waiting for the signal
     * if the process instance contains multiple executions.
     *
     * @param executionId id of process instance or execution to signal, cannot be null.
     * @param signalName name of the signal (can be null)
     * @param signalData additional data of the signal (can be null)
     * @param processVariables a map of process variables (can be null)
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function signal(/*?string*/$executionId, ?string $signalName = null, $signalData = null, array $processVariables = []): void;

    /**
     * The variable values for all given variableNames, takes all variables into account which are visible from the given execution scope (including parent scopes).
     *
     * @param executionId id of process instance or execution, cannot be null.
     * @param variableNames the collection of variable names that should be retrieved.
     *
     * @return array the variables or an empty map if no such variables are found.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          <li>if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE or
     *          no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li> In case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled and
     *          the user has no ProcessDefinitionPermisions#READ_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function getVariables(?string $executionId, array $variableNames = []): VariableMapInterface;

    /**
     * The variable values for all given variableNames, takes all variables into account which are visible from the given execution scope (including parent scopes).
     * @param executionId id of process instance or execution, cannot be null.
     * @param variableNames the collection of variable names that should be retrieved.
     * @param deserializeObjectValues if false, SerializableValues will not be deserialized
     *
     * @return VariableMapInterface the variables or an empty map if no such variables are found.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          <li>if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE or
     *          no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li> In case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled and
     *          the user has no ProcessDefinitionPermisions#READ_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *
     * @since 7.2
     *
     */
    public function getVariablesTyped(?string $executionId, array $variableNames = [], bool $deserializeValues = true): VariableMapInterface;

    /**
     * The variable values for the given variableNames only taking the given execution scope into account, not looking in outer scopes.
     *
     * @param executionId id of execution, cannot be null.
     * @param variableNames the collection of variable names that should be retrieved.
     *
     * @return array the variables or an empty map if no such variables are found.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          <li>if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE or
     *          no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li> In case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled and
     *          the user has no ProcessDefinitionPermisions#READ_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function getVariablesLocal(?string $executionId, array $variableNames = []): VariableMapInterface;

    /**
     * The variable values for the given variableNames only taking the given execution scope into account, not looking in outer scopes.
     * @param executionId id of execution, cannot be null.
     * @param variableNames the collection of variable names that should be retrieved.
     * @param deserializeObjectValues if false, SerializableValues will not be deserialized
     *
     * @return VariableMapInterface the variables or an empty map if no such variables are found.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          <li>if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE or
     *          no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li> In case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled and
     *          the user has no ProcessDefinitionPermisions#READ_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *
     * @since 7.2
     *
     */
    public function getVariablesLocalTyped(?string $executionId, array $variableNames = [], bool $deserializeValues = true): VariableMapInterface;

    /**
     * The variable value.  Searching for the variable is done in all scopes that are visible to the given execution (including parent scopes).
     * Returns null when no variable value is found with the given name or when the value is set to null.
     *
     * @param executionId id of process instance or execution, cannot be null.
     * @param variableName name of variable, cannot be null.
     *
     * @return mixed the variable value or null if the variable is undefined or the value of the variable is null.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          <li>if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE or
     *          no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li> In case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled and
     *          the user has no ProcessDefinitionPermisions#READ_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function getVariable(?string $executionId, ?string $variableName);

    /**
     * Returns a TypedValue for the variable. Searching for the variable is done in all scopes that are visible
     * to the given execution (including parent scopes). Returns null when no variable value is found with the given name.
     *
     * @param executionId id of process instance or execution, cannot be null.
     * @param variableName name of variable, cannot be null.
     * @param deserializeValue if false, a SerializableValue will not be deserialized
     *
     * @return TypedValueInterface the variable value or null if the variable is undefined.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          <li>if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE or
     *          no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li> In case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled and
     *          the user has no ProcessDefinitionPermisions#READ_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *
     * @since 7.2
     *
     */
    public function getVariableTyped(?string $executionId, ?string $variableName, bool $deserializeValue = true): ?TypedValueInterface;

    /**
     * The variable value for an execution. Returns the value when the variable is set
     * for the execution (and not searching parent scopes). Returns null when no variable value is found with the given name or when the value is set to null.
     *
     * @param executionId id of process instance or execution, cannot be null.
     * @param variableName name of variable, cannot be null.
     *
     * @return mixed variable value or null if the variable is undefined or the value of the variable is null.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          <li>if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE or
     *          no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li> In case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled and
     *          the user has no ProcessDefinitionPermisions#READ_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function getVariableLocal(?string $executionId, ?string $variableName);

    /**
     * Returns a TypedValue for the variable. Searching for the variable is done in all scopes that are visible
     * to the given execution (and not searching parent scopes). Returns null when no variable value is found with the given name.
     *
     * @param executionId id of process instance or execution, cannot be null.
     * @param variableName name of variable, cannot be null.
     * @param deserializeValue if false, a SerializableValue will not be deserialized
     *
     * @return TypedValueInterface the variable value or null if the variable is undefined.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          <li>if the user has no Permissions#READ permission on Resources#PROCESS_INSTANCE or
     *          no Permissions#READ_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li> In case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} config is enabled and
     *          the user has no ProcessDefinitionPermisions#READ_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *
     * @since 7.2
     *
     */
    public function getVariableLocalTyped(?string $executionId, ?string $variableName, bool $deserializeValue = true): ?TypedValueInterface;

    /**
     * Update or create a variable for an execution.  If the variable does not already exist
     * somewhere in the execution hierarchy (i.e. the specified execution or any ancestor),
     * it will be created in the process instance (which is the root execution).
     *
     * @param executionId id of process instance or execution to set variable in, cannot be null.
     * @param variableName name of variable to set, cannot be null.
     * @param value value to set. When null is passed, the variable is not removed,
     * only it's value will be set to null.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#UPDATE_VARIABLE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#UPDATE_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function setVariable(?string $executionId, ?string $variableName, $value): void;

    /**
     * Update or create a variable for an execution (not considering parent scopes).
     * If the variable does not already exist, it will be created in the given execution.
     *
     * @param executionId id of execution to set variable in, cannot be null.
     * @param variableName name of variable to set, cannot be null.
     * @param value value to set. When null is passed, the variable is not removed,
     * only it's value will be set to null.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#UPDATE_VARIABLE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#UPDATE_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function setVariableLocal(?string $executionId, ?string $variableName, $value): void;

    /**
     * Update or create given variables for an execution (including parent scopes). If the variables are not already existing, they will be created in the process instance
     * (which is the root execution).
     *
     * @param executionId id of the process instance or the execution, cannot be null.
     * @param variables map containing name (key) and value of variables, can be null.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#UPDATE_VARIABLE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#UPDATE_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function setVariables(?string $executionId, array $variables = []): void;

    /**
     * Update or create given variables for an execution (not considering parent scopes). If the variables are not already existing, it will be created in the given execution.
     *
     * @param executionId id of the execution, cannot be null.
     * @param variables map containing name (key) and value of variables, can be null.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#UPDATE_VARIABLE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#UPDATE_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function setVariablesLocal(?string $executionId, array $variables = []): void;

    /**
     * Update or create runtime process variables in the root scope of process instances.
     *
     * @param processInstanceIds related to the process instances the variables will be set on; cannot
     *                           be {@code null} when processInstanceQuery and
     *                           historicProcessInstanceQuery are {@code null}.
     * @param processInstanceQuery to select process instances; Cannot be {@code null} when
     *                             processInstanceIds and historicProcessInstanceQuery
     *                             are {@code null}.
     * @param historicProcessInstanceQuery to select process instances; Cannot be {@code null} when
     *                                     processInstanceIds and processInstanceQuery
     *                                     are {@code null}.
     * @param variables that will be set to the root scope of the process instances
     *
     * @throws NullValueException <ul>
     *   <li>when {@code variables} is {@code null}</li>
     *   <li>when {@code processInstanceIds}, {@code processInstanceQuery} and
     *   {@code historicProcessInstanceQuery} are {@code null}</li>
     * </ul>
     * @throws BadUserRequestException <ul>
     *   <li>when {@code variables} is empty</li>
     *   <li>when no process instance ids were found</li>
     *   <li>when a transient variable is set</li>
     * </ul>
     * @throws ProcessEngineException when the java serialization format is prohibited
     * @throws AuthorizationException when the user has no BatchPermissions#CREATE or
     * BatchPermissions#CREATE_BATCH_SET_VARIABLES permission on Resources#BATCH.
     *
     * @return BatchInterface the batch which sets the variables asynchronously.
     */
    public function setVariablesAsync(
        array $processInstanceIds,
        ProcessInstanceQueryInterface $processInstanceQuery = null,
        HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery = null,
        array $variables = []
    ): BatchInterface;

    /**
     * Removes a variable for an execution.
     *
     * @param executionId id of process instance or execution to remove variable in.
     * @param variableName name of variable to remove.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#UPDATE_VARIABLE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#UPDATE_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function removeVariable(?string $executionId, ?string $variableName): void;

    /**
     * Removes a variable for an execution (not considering parent scopes).
     *
     * @param executionId id of execution to remove variable in.
     * @param variableName name of variable to remove.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#UPDATE_VARIABLE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#UPDATE_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function removeVariableLocal(?string $executionId, ?string $variableName): void;

    /**
     * Removes variables for an execution.
     *
     * @param executionId id of process instance or execution to remove variable in.
     * @param variableNames collection containing name of variables to remove.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#UPDATE_VARIABLE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#UPDATE_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function removeVariables(?string $executionId, ?array $variableNames = []): void;

    /**
     * Remove variables for an execution (not considering parent scopes).
     *
     * @param executionId id of execution to remove variable in.
     * @param variableNames collection containing name of variables to remove.
     *
     * @throws ProcessEngineException
     *          when no execution is found for the given executionId.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#UPDATE_VARIABLE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#UPDATE_INSTANCE_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function removeVariablesLocal(?string $executionId, ?array $variableNames = []): void;

    // Queries ////////////////////////////////////////////////////////

    /** Creates a new ExecutionQuery instance,
     * that can be used to query the executions and process instances. */
    public function createExecutionQuery(): ExecutionQueryInterface;

    /**
     * creates a new NativeExecutionQuery to query Executions
     * by SQL directly
     */
    public function createNativeExecutionQuery(): NativeExecutionQueryInterface;

    /**
     * Creates a new ProcessInstanceQuery instance, that can be used
     * to query process instances.
     */
    public function createProcessInstanceQuery(): ProcessInstanceQueryInterface;

    /**
     * creates a new NativeProcessInstanceQuery to query ProcessInstances
     * by SQL directly
     */
    public function createNativeProcessInstanceQuery(): NativeProcessInstanceQueryInterface;

    /**
     * Creates a new IncidentQuery instance, that can be used
     * to query incidents.
     */
    public function createIncidentQuery(): IncidentQueryInterface;

    /**
     * Creates a new EventSubscriptionQuery instance, that can be used to query
     * event subscriptions.
     */
    public function createEventSubscriptionQuery(): EventSubscriptionQueryInterface;

    /**
     * Creates a new VariableInstanceQuery instance, that can be used to query
     * variable instances.
     */
    public function createVariableInstanceQuery(): VariableInstanceQueryInterface;

    // Process instance state //////////////////////////////////////////

    /**
     * <p>Suspends the process instance with the given id. This means that the
     * execution is stopped, so the <i>token state</i> will not change.
     * However, actions that do not change token state, like setting/removing
     * variables, etc. will succeed.</p>
     *
     * <p>Tasks belonging to this process instance will also be suspended. This means
     * that any actions influencing the tasks' lifecycles will fail, such as
     * <ul>
     *   <li>claiming</li>
     *   <li>completing</li>
     *   <li>delegation</li>
     *   <li>changes in task assignees, owners, etc.</li>
     * </ul>
     * Actions that only change task properties will succeed, such as changing variables
     * or adding comments.
     * </p>
     *
     * <p>If a process instance is in state suspended, the engine will also not
     * execute jobs (timers, messages) associated with this instance.</p>
     *
     * <p>If you have a process instance hierarchy, suspending
     * one process instance from the hierarchy will not suspend other
     * process instances from that hierarchy.</p>
     *
     * <p>Note: for more complex suspend commands use {@link #updateProcessInstanceSuspensionState()}.</p>
     *
     * @throws ProcessEngineException
     *          if no such processInstance can be found.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#SUSPEND permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#SUSPEND_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function suspendProcessInstanceById(?string $processInstanceId): void;

    /**
     * <p>Suspends the process instances with the given process definition id.
     * This means that the execution is stopped, so the <i>token state</i>
     * will not change. However, actions that do not change token state, like
     * setting/removing variables, etc. will succeed.</p>
     *
     * <p>Tasks belonging to the suspended process instance will also be suspended.
     * This means that any actions influencing the tasks' lifecycles will fail, such as
     * <ul>
     *   <li>claiming</li>
     *   <li>completing</li>
     *   <li>delegation</li>
     *   <li>changes in task assignees, owners, etc.</li>
     * </ul>
     * Actions that only change task properties will succeed, such as changing variables
     * or adding comments.
     * </p>
     *
     * <p>If a process instance is in state suspended, the engine will also not
     * execute jobs (timers, messages) associated with this instance.</p>
     *
     * <p>If you have a process instance hierarchy, suspending
     * one process instance from the hierarchy will not suspend other
     * process instances from that hierarchy.</p>
     *
     * <p>Note: for more complex suspend commands use {@link #updateProcessInstanceSuspensionState()}.</p>
     *
     * @throws ProcessEngineException
     *          if no such processInstance can be found.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#SUSPEND permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#SUSPEND_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function suspendProcessInstanceByProcessDefinitionId(?string $processDefinitionId): void;

    /**
     * <p>Suspends the process instances with the given process definition key.
     * This means that the execution is stopped, so the <i>token state</i>
     * will not change. However, actions that do not change token state, like
     * setting/removing variables, etc. will succeed.</p>
     *
     * <p>Tasks belonging to the suspended process instance will also be suspended.
     * This means that any actions influencing the tasks' lifecycles will fail, such as
     * <ul>
     *   <li>claiming</li>
     *   <li>completing</li>
     *   <li>delegation</li>
     *   <li>changes in task assignees, owners, etc.</li>
     * </ul>
     * Actions that only change task properties will succeed, such as changing variables
     * or adding comments.
     * </p>
     *
     * <p>If a process instance is in state suspended, the engine will also not
     * execute jobs (timers, messages) associated with this instance.</p>
     *
     * <p>If you have a process instance hierarchy, suspending
     * one process instance from the hierarchy will not suspend other
     * process instances from that hierarchy.</p>
     *
     * <p>Note: for more complex suspend commands use {@link #updateProcessInstanceSuspensionState()}.</p>
     *
     * @throws ProcessEngineException
     *          if no such processInstance can be found.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#SUSPEND permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#SUSPEND_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function suspendProcessInstanceByProcessDefinitionKey(?string $processDefinitionKey): void;

    /**
     * <p>Activates the process instance with the given id.</p>
     *
     * <p>If you have a process instance hierarchy, activating
     * one process instance from the hierarchy will not activate other
     * process instances from that hierarchy.</p>
     *
     * <p>Note: for more complex activate commands use {@link #updateProcessInstanceSuspensionState()}.</p>
     *
     * @throws ProcessEngineException
     *          if no such processInstance can be found.
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#SUSPEND permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#SUSPEND_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function activateProcessInstanceById(?string $processInstanceId): void;

    /**
     * <p>Activates the process instance with the given process definition id.</p>
     *
     * <p>If you have a process instance hierarchy, activating
     * one process instance from the hierarchy will not activate other
     * process instances from that hierarchy.</p>
     *
     * <p>Note: for more complex activate commands use {@link #updateProcessInstanceSuspensionState()}.</p>
     *
     * @throws ProcessEngineException
     *          if the process definition id is null
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#SUSPEND permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#SUSPEND_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function activateProcessInstanceByProcessDefinitionId(?string $processDefinitionId): void;

    /**
     * <p>Activates the process instance with the given process definition key.</p>
     *
     * <p>If you have a process instance hierarchy, activating
     * one process instance from the hierarchy will not activate other
     * process instances from that hierarchy.</p>
     *
     * <p>Note: for more complex activate commands use {@link #updateProcessInstanceSuspensionState()}.</p>
     *
     * @throws ProcessEngineException
     *          if the process definition id is null
     * @throws AuthorizationException
     *          if the user has none of the following:
     *          <li>ProcessInstancePermissions#SUSPEND permission on Resources#PROCESS_INSTANCE</li>
     *          <li>ProcessDefinitionPermissions#SUSPEND_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *          <li>Permissions#UPDATE permission on Resources#PROCESS_INSTANCE</li>
     *          <li>Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     */
    public function activateProcessInstanceByProcessDefinitionKey(?string $processDefinitionKey): void;

    /**
     * Activate or suspend process instances using a fluent builder. Specify the
     * instances by calling one of the <i>by</i> methods, like
     * <i>byProcessInstanceId</i>. To update the suspension state call
     * UpdateProcessInstanceSuspensionStateBuilder#activate() or
     * UpdateProcessInstanceSuspensionStateBuilder#suspend().
     *
     * @return UpdateProcessInstanceSuspensionStateSelectBuilderInterface the builder to update the suspension state
     */
    public function updateProcessInstanceSuspensionState(): UpdateProcessInstanceSuspensionStateSelectBuilderInterface;

    // Events ////////////////////////////////////////////////////////////////////////

    /**
     * Notifies the process engine that a signal event of name 'signalName' has
     * been received. This method delivers the signal to a single execution, being the
     * execution referenced by 'executionId'.
     * The waiting execution is notified synchronously.
     *
     * Note that you need to provide the exact execution that is waiting for the signal
     * if the process instance contains multiple executions.
     *
     * @param signalName
     *          the name of the signal event
     * @param executionId
     *          the id of the process instance or the execution to deliver the signal to
     * @param processVariables
     *          a map of variables added to the execution(s)
     *
     * @throws ProcessEngineException
     *          if no such execution exists or if the execution
     *          has not subscribed to the signal
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function signalEventReceived(?string $signalName, ?string $executionId = null, array $processVariables = []): void;

    /**
     * Notifies the process engine that a signal event has been received using a
     * fluent builder.
     *
     * @param signalName
     *          the name of the signal event
     * @return SignalEventReceivedBuilderInterface the fluent builder to send the signal
     */
    public function createSignalEvent(?string $signalName): SignalEventReceivedBuilderInterface;

    /**
     * Notifies the process engine that a message event with the name 'messageName' has
     * been received and has been correlated to an execution with id 'executionId'.
     *
     * The waiting execution is notified synchronously.
     *
     * Note that you need to provide the exact execution that is waiting for the message
     * if the process instance contains multiple executions.
     *
     * @param messageName
     *          the name of the message event
     * @param executionId
     *          the id of the process instance or the execution to deliver the message to
     * @param processVariables
     *          a map of variables added to the execution
     *
     * @throws ProcessEngineException
     *          if no such execution exists or if the execution
     *          has not subscribed to the signal
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function messageEventReceived(?string $messageName, ?string $executionId, array $processVariables = []): void;

    /**
     * Define a complex message correlation using a fluent builder.
     *
     * @param messageName the name of the message. Corresponds to the 'name' element
     * of the message defined in BPMN 2.0 Xml.
     * Can be null to correlate by other criteria (businessKey, processInstanceId, correlationKeys) only.
     *
     * @return MessageCorrelationBuilderInterface the fluent builder for defining the message correlation.
     */
    public function createMessageCorrelation(?string $messageName): MessageCorrelationBuilderInterface;

    /**
     * Correlates a message to
     * <ul>
     *  <li>
     *    an execution that is waiting for a matching message and can be correlated according
     *    to the given correlation keys. This is typically matched against process instance variables.
     *    The process instance it belongs to has to have the given business key.
     *  </li>
     *  <li>
     *    a process definition that can be started by this message.
     *  </li>
     * </ul>
     * and updates the process instance variables.
     *
     * Notification and instantiation happen synchronously.
     *
     * @param messageName
     *          the name of the message event; if null, matches any event
     * @param businessKey
     *          the business key of process instances to correlate against
     * @param correlationKeys
     *          a map of key value pairs that are used to correlate the message to an execution
     * @param processVariables
     *          a map of variables added to the execution or newly created process instance
     *
     * @throws MismatchingMessageCorrelationException
     *          if none or more than one execution or process definition is correlated
     * @throws ProcessEngineException
     *          if messageName is null and businessKey is null and correlationKeys is null
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function correlateMessage(?string $messageName, ?string $businessKey = null, array $correlationKeys = null, array $processVariables = null): void;

    /**
     * Define a modification of a process instance in terms of activity cancellations
     * and instantiations via a fluent builder. Instructions are executed in the order they are specified.
     *
     * @param processInstanceId the process instance to modify
     */
    public function createProcessInstanceModification(?string $processInstanceId): ProcessInstanceModificationBuilderInterface;

    /**
     * Returns a fluent builder to start a new process instance in the exactly
     * specified version of the process definition with the given id. The builder
     * can be used to set further properties and specify instantiation
     * instructions to start the instance at any set of activities in the process.
     * If no instantiation instructions are set then the instance start at the
     * default start activity.
     *
     * @param processDefinitionId
     *          the id of the process definition, cannot be <code>null</code>.
     *
     * @return a builder to create a process instance of the definition
     */
    public function createProcessInstanceById(?string $processDefinitionId): ProcessInstantiationBuilderInterface;

    /**
     * Returns a fluent builder to start a new process instance in the latest
     * version of the process definition with the given key. The builder can be
     * used to set further properties and specify instantiation instructions to
     * start the instance at any set of activities in the process. If no
     * instantiation instructions are set then the instance start at the default
     * start activity.
     *
     * @param processDefinitionKey
     *          the key of the process definition, cannot be <code>null</code>.
     *
     * @return a builder to create a process instance of the definition
     */
    public function createProcessInstanceByKey(?string $processDefinitionKey): ProcessInstantiationBuilderInterface;

    /**
     * Creates a migration plan to migrate process instance between different process definitions.
     * Returns a fluent builder that can be used to specify migration instructions and build the plan.
     *
     * @param sourceProcessDefinitionId the process definition that instances are migrated from
     * @param targetProcessDefinitionId the process definition that instances are migrated to
     * @return a fluent builder
     */
    public function createMigrationPlan(?string $sourceProcessDefinitionId, ?string $targetProcessDefinitionId): MigrationPlanBuilderInterface;

    /**
     * Executes a migration plan for a given list of process instances. The migration can
     * either be executed synchronously or asynchronously. A synchronously migration
     * blocks the caller until the migration was completed. The migration can only be
     * successfully completed if all process instances can be migrated.
     *
     * If the migration is executed asynchronously a Batch is immediately returned.
     * The migration is then executed as jobs from the process engine and the batch can
     * be used to track the progress of the migration. The Batch splits the migration
     * in smaller chunks which will be executed independently.
     *
     * @param migrationPlan the migration plan to executed
     * @return a fluent builder
     */
    public function newMigration(MigrationPlanInterface $migrationPlan): MigrationPlanExecutionBuilderInterface;

    /**
     * Creates a modification of multiple process instances in terms of activity cancellations
     * and instantiations via a fluent builder. Returns a fluent builder that can be used to specify
     * modification instructions and set process instances that should be modified.
     *
     * The modification can
     * either be executed synchronously or asynchronously. A synchronously modification
     * blocks the caller until the modification was completed. The modification can only be
     * successfully completed if all process instances can be modified.
     *
     * If the modification is executed asynchronously a Batch is immediately returned.
     * The modification is then executed as jobs from the process engine and the batch can
     * be used to track the progress of the modification. The Batch splits the modification
     * in smaller chunks which will be executed independently.
     *
     * @param processDefinitionId the process definition that instances are modified of
     * @return a fluent builder
     */

    public function createModification(?string $processDefinitionId): ModificationBuilderInterface;

    /**
     * Restarts process instances that are completed or deleted with the initial or last set of variables.
     *
     * @param processDefinitionId the id of the process definition, cannot be null.
     *
     * @throws ProcessEngineException
     *          when no process definition is deployed with the given key or a process instance is still active.
     * @throws AuthorizationException
     *          if the user has not all of the following permissions
     *     <ul>
     *       <li>Permissions#CREATE permission on Resources#PROCESS_INSTANCE</li>
     *       <li>Permissions#CREATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *       <li>Permissions#READ_HISTORY permission on Resources#PROCESS_DEFINITION</li>
     *     </ul>
     */
    public function restartProcessInstances(?string $processDefinitionId): RestartProcessInstanceBuilderInterface;

    /**
     * Creates an incident
     *
     * @param incidentType the type of incident, cannot be null
     * @param executionId execution id, cannot be null
     * @param configuration
     * @param message
     *
     * @return a new incident
     *
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          and no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function createIncident(?string $incidentType, ?string $executionId, ?string $configuration, ?string $message = null): IncidentInterface;

    /**
     * Resolves and remove an incident
     *
     * @param incidentId the id of an incident to resolve
     *
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          and no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     */
    public function resolveIncident(?string $incidentId): void;

    /**
     * Set an annotation to an incident.
     *
     * @throws NotValidException when incident id is {@code null}
     * @throws BadUserRequestException when no incident could be found
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          and no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     *
     * @param incidentId of the incident that the annotation is updated at
     * @param annotation that is set to the incident
     *
     * @since 7.15
     */
    public function setAnnotationForIncidentById(?string $incidentId, ?string $annotation): void;

    /**
     * Clear the annotation for an incident.
     *
     * @throws NotValidException when incident id is {@code null}
     * @throws BadUserRequestException when no incident could be found
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          and no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     *
     * @param incidentId of the incident that the annotation is cleared at
     *
     * @since 7.15
     */
    public function clearAnnotationForIncidentById(?string $incidentId): void;

    /**
     * Define a complex condition evaluation using a fluent builder.
     *
     * @return ConditionEvaluationBuilderInterface the fluent builder for defining the condition evaluation.
     */
    public function createConditionEvaluation(): ConditionEvaluationBuilderInterface;
}
