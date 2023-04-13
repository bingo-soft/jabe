<?php

namespace Jabe;

use Jabe\Batch\History\HistoricBatchQueryInterface;
use Jabe\History\{
    CleanableHistoricBatchReportInterface,
    CleanableHistoricProcessInstanceReportInterface,
    NativeHistoricProcessInstanceQueryInterface,
    NativeHistoricVariableInstanceQueryInterface,
    HistoricActivityStatisticsQueryInterface,
    HistoricDecisionInstanceStatisticsQueryInterface,
    HistoricDetailQueryInterface,
    HistoricExternalTaskLogQueryInterface,
    HistoricJobLogQueryInterface,
    HistoricIncidentQueryInterface,
    HistoricIdentityLinkLogQueryInterface,
    HistoricProcessInstanceReportInterface,
    HistoricTaskInstanceReportInterface,
    UserOperationLogQueryInterface,
    HistoricActivityInstanceQueryInterface,
    HistoricProcessInstanceQueryInterface,
    HistoricTaskInstanceQueryInterface,
    HistoricVariableInstanceQueryInterface,
    NativeHistoricActivityInstanceQueryInterface,
    NativeHistoricTaskInstanceQueryInterface,
    SetRemovalTimeSelectModeForHistoricBatchesBuilderInterface,
    SetRemovalTimeSelectModeForHistoricProcessInstancesBuilderInterface
};
use Jabe\Runtime\JobInterface;
use Jabe\Batch\BatchInterface;

interface HistoryServiceInterface
{
    /**
     * <p>Creates a new programmatic query to search for HistoricProcessInstances.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *   Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true})
     * */
    public function createHistoricProcessInstanceQuery(): HistoricProcessInstanceQueryInterface;

    /**
     * <p>Creates a new programmatic query to search for HistoricActivityInstances.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *   Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true})
     *
     * */
    public function createHistoricActivityInstanceQuery(): HistoricActivityInstanceQueryInterface;

    /**
     * <p>Query for the number of historic activity instances aggregated by activities of a single
     * process definition.
     *
     * <p>The result of the query is empty when the user has no Permissions#READ_HISTORY
     * permission on Resources#PROCESS_DEFINITION
     */
    public function createHistoricActivityStatisticsQuery(?string $processDefinitionId): HistoricActivityStatisticsQueryInterface;

    /**
     * <p>Creates a new programmatic query to search for HistoricTaskInstances.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *   Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true}) OR
     *   <li>The user has no HistoricTaskPermissions#READ permission on
     *       Resources#HISTORIC_TASK ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true})
     * */
    public function createHistoricTaskInstanceQuery(): HistoricTaskInstanceQueryInterface;

    /**
     * <p>Creates a new programmatic query to search for HistoricDetails.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *       Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricTaskPermissions#READ permission on
     *       Resources#HISTORIC_TASK ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true}) OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true}) OR
     *   <li>The user has no ProcessDefinitionPermissions#READ_HISTORY_VARIABLE permission on
     *       Resources#PROCESS_DEFINITION
     *       (ProcessEngineConfigurationImpl#enforceSpecificVariablePermission must be set to
     *       {@code true}) OR
     *   <li>The user has no HistoricTaskPermissions#READ_VARIABLE permission on
     *       Resources#HISTORIC_TASK ({@code enforceSpecificVariablePermission} and
     *       {@code enableHistoricInstancePermissions}
     *       in ProcessEngineConfigurationImpl must be set to {@code true})
     * */
    public function createHistoricDetailQuery(): HistoricDetailQueryInterface;

    /**
     * <p>Creates a new programmatic query to search for HistoricVariableInstances.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *       Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricTaskPermissions#READ permission on
     *       Resources#HISTORIC_TASK ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true}) OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true}) OR
     *   <li>The user has no ProcessDefinitionPermissions#READ_HISTORY_VARIABLE permission on
     *       Resources#PROCESS_DEFINITION
     *       (ProcessEngineConfigurationImpl#enforceSpecificVariablePermission must be set to
     *       {@code true}) OR
     *   <li>The user has no HistoricTaskPermissions#READ_VARIABLE permission on
     *       Resources#HISTORIC_TASK ({@code enforceSpecificVariablePermission} and
     *       {@code enableHistoricInstancePermissions}
     *       in ProcessEngineConfigurationImpl must be set to {@code true})
     * */
    public function createHistoricVariableInstanceQuery(): HistoricVariableInstanceQueryInterface;

    /** <p>Creates a new programmatic query to search for UserOperationLogEntry instances.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *   Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true}) OR
     *   <li>The user has no HistoricTaskPermissions#READ permission on
     *       Resources#HISTORIC_TASK ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true})
     *
     * */
    public function createUserOperationLogQuery(): UserOperationLogQueryInterface;

    /**
     * <p>Creates a new programmatic query to search for {@link HistoricIncident historic incidents}.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *   Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true})
     * */
    public function createHistoricIncidentQuery(): HistoricIncidentQueryInterface;

    /**
     * <p>Creates a new programmatic query to search for
     * {@link HistoricIdentityLinkLog historic identity links}.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *   Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricTaskPermissions#READ permission on
     *       Resources#HISTORIC_TASK ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true})
     * */
    public function createHistoricIdentityLinkLogQuery(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Deletes historic task instance.  This might be useful for tasks that are
     * {@link TaskService#newTask() dynamically created} and then {@link TaskService#complete(String) completed}.
     * If the historic task instance doesn't exist, no exception is thrown and the
     * method returns normal.
     *
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteHistoricTaskInstance(?string $taskId): void;

    /**
     * Deletes historic process instance. All historic activities, historic task and
     * historic details (variable updates, form properties) are deleted as well.
     *
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteHistoricProcessInstance(?string $processInstanceId): void;

    /**
     * Deletes historic process instance. All historic activities, historic task and
     * historic details (variable updates, form properties) are deleted as well.
     * Does not fail if a process instance was not found.
     *
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteHistoricProcessInstanceIfExists(?string $processInstanceId): void;

    /**
     * Deletes historic process instances. All historic activities, historic task and
     * historic details (variable updates, form properties) are deleted as well.
     *
     * @throws BadUserRequestException
     *          when no process instances are found with the given ids or ids are null.
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteHistoricProcessInstances(array $processInstanceIds): void;

    /**
     * Deletes historic process instances. All historic activities, historic task and
     * historic details (variable updates, form properties) are deleted as well. Does not
     * fail if a process instance was not found.
     *
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteHistoricProcessInstancesIfExists(array $processInstanceIds): void;

    /**
     * Deletes historic process instances and all related historic data in bulk manner. DELETE SQL statement will be created for each entity type. They will have list
     * of given process instance ids in IN clause. Therefore, DB limitation for number of values in IN clause must be taken into account.
     *
     * @param processInstanceIds list of process instance ids for removal
     *
     * @throws BadUserRequestException
     *          when no process instances are found with the given ids or ids are null or when some of the process instances are not finished yet
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteHistoricProcessInstancesBulk(array $processInstanceIds): void;

    /**
     * Schedules history cleanup job at batch window start time. The job will delete historic data for
     * finished process, decision and case instances, and batch operations taking into account ProcessDefinition#getHistoryTimeToLive(),
     * DecisionDefinition#getHistoryTimeToLive(), CaseDefinition#getHistoryTimeToLive(), ProcessEngineConfigurationImpl#getBatchOperationHistoryTimeToLive()
     * and ProcessEngineConfigurationImpl#getBatchOperationsForHistoryCleanup() values.
     *
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE_HISTORY permission on Resources#PROCESS_DEFINITION
     * @return history cleanup job. NB! As of v. 7.9.0, method does not guarantee to return a job. Use {@link #findHistoryCleanupJobs()} instead.
     */
    public function cleanUpHistoryAsync(bool $immediatelyDue = false): JobInterface;

    /**
     * Finds history cleanup job if present.
     * @return job entity
     */
    public function findHistoryCleanupJobs(): array;

    /**
     * Deletes historic process instances asynchronously. All historic activities, historic task and
     * historic details (variable updates, form properties) are deleted as well.
     *
     * @throws BadUserRequestException
     *          when no process instances is found with the given ids or ids are null.
     * @throws AuthorizationException
     *          If the user has no Permissions#CREATE or
     *          BatchPermissions#CREATE_BATCH_DELETE_FINISHED_PROCESS_INSTANCES permission on Resources#BATCH.
     */
    public function deleteHistoricProcessInstancesAsync(array $processInstanceIds, ?HistoricProcessInstanceQueryInterface $query, ?string $deleteReason): BatchInterface;

    /**
     * Deletes a user operation log entry. Does not cascade to any related entities.
     *
     * @throws AuthorizationException
     *           For entries related to process definition keys: If the user has
     *           neither Permissions#DELETE_HISTORY permission on
     *           Resources#PROCESS_DEFINITION nor
     *           UserOperationLogCategoryPermissions#DELETE permission on
     *           Resources#OPERATION_LOG_CATEGORY. For entries not related
     *           to process definition keys: If the user has no
     *           UserOperationLogCategoryPermissions#DELETE permission on
     *           Resources#OPERATION_LOG_CATEGORY.
     */
    public function deleteUserOperationLogEntry(?string $entryId): void;

    /**
     * Deletes a historic variable instance by its id. All related historic
     * details (variable updates, form properties) are deleted as well.
     *
     * @param variableInstanceId
     *          the id of the variable instance
     * @throws BadUserRequestException
     *           when the historic variable instance is not found by the given id
     *           or if id is null
     * @throws AuthorizationException
     *           If the variable instance has a process definition key and
     *           the user has no Permissions#DELETE_HISTORY permission on
     *           Resources#PROCESS_DEFINITION.
     */
    public function deleteHistoricVariableInstance(?string $variableInstanceId): void;

    /**
     * Deletes all historic variables and historic details (variable updates, form properties) of a process instance.
     *
     * @param processInstanceId
     *          the id of the process instance
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function deleteHistoricVariableInstancesByProcessInstanceId(?string $processInstanceId): void;

    /**
     * creates a native query to search for HistoricProcessInstances via SQL
     */
    public function createNativeHistoricProcessInstanceQuery(): NativeHistoricProcessInstanceQueryInterface;

    /**
     * creates a native query to search for HistoricTaskInstances via SQL
     */
    public function createNativeHistoricTaskInstanceQuery(): NativeHistoricTaskInstanceQueryInterface;

    /**
     * creates a native query to search for HistoricActivityInstances via SQL
     */
    public function createNativeHistoricActivityInstanceQuery(): NativeHistoricActivityInstanceQueryInterface;

    /**
     * creates a native query to search for HistoricVariableInstances via SQL
     */
    public function createNativeHistoricVariableInstanceQuery(): NativeHistoricVariableInstanceQueryInterface;

    /**
     * <p>Creates a new programmatic query to search for {@link HistoricJobLog historic job logs}.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *   Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true})
     */
    public function createHistoricJobLogQuery(): HistoricJobLogQueryInterface;

    /**
     * Returns the full stacktrace of the exception that occurs when the
     * historic job log with the given id was last executed. Returns null
     * when the historic job log has no exception stacktrace.
     *
     * @param historicJobLogId id of the historic job log, cannot be null.
     * @throws ProcessEngineException when no historic job log exists with the given id.
     *
     * @throws AuthorizationException
     *          If the user has no Permissions#READ_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function getHistoricJobLogExceptionStacktrace(?string $historicJobLogId): ?string;

    /**
     * Creates a new programmatic query to create a historic process instance report.
     */
    public function createHistoricProcessInstanceReport(): HistoricProcessInstanceReportInterface;

    /**
     * <p>Creates a new programmatic query to create a historic task instance report.
     *
     * <p>Subsequent builder methods throw AuthorizationException when the user has no
     * Permissions#READ_HISTORY permission on any Resources#PROCESS_DEFINITION.
     */
    public function createHistoricTaskInstanceReport(): HistoricTaskInstanceReportInterface;

    /**
     * Creates a new programmatic query to create a cleanable historic process instance report.
     */
    public function createCleanableHistoricProcessInstanceReport(): CleanableHistoricProcessInstanceReportInterface;

    /**
     * Creates a new programmatic query to create a cleanable historic batch report.
     */
    public function createCleanableHistoricBatchReport(): CleanableHistoricBatchReportInterface;

    /**
     * Creates a query to search for HistoricBatch instances.
     */
    public function createHistoricBatchQuery(): HistoricBatchQueryInterface;

    /**
     * Deletes a historic batch instance. All corresponding historic job logs are deleted as well;
     *
     * @throws AuthorizationException
     *          If the user has no Permissions#DELETE permission on Resources#BATCH
     */
    public function deleteHistoricBatch(?string $id): void;


    /**
     * Query for the statistics of DRD evaluation.
     *
     * @param decisionRequirementsDefinitionId id of decision requirement definition
     */
    //public function createHistoricDecisionInstanceStatisticsQuery(?string $decisionRequirementsDefinitionId): HistoricDecisionInstanceStatisticsQueryInterface;

    /**
     * <p>Creates a new programmatic query to search for
     * {@link HistoricExternalTaskLog historic external task logs}.
     *
     * <p>The result of the query is empty in the following cases:
     * <ul>
     *   <li>The user has no Permissions#READ_HISTORY permission on
     *   Resources#PROCESS_DEFINITION OR
     *   <li>The user has no HistoricProcessInstancePermissions#READ permission on
     *       Resources#HISTORIC_PROCESS_INSTANCE ({@code enableHistoricInstancePermissions} in
     *       ProcessEngineConfigurationImpl must be set to {@code true})
     */
    public function createHistoricExternalTaskLogQuery(): HistoricExternalTaskLogQueryInterface;

    /**
     * Returns the full error details that occurs when the
     * historic external task log with the given id was last executed. Returns null
     * when the historic external task log contains no error details.
     *
     * @param historicExternalTaskLogId id of the historic external task log, cannot be null.
     * @throws ProcessEngineException when no historic external task log exists with the given id.
     *
     * @throws AuthorizationException
     *          If the user has no Permissions#READ_HISTORY permission on Resources#PROCESS_DEFINITION.
     */
    public function getHistoricExternalTaskLogErrorDetails(?string $historicExternalTaskLogId): ?string;

    /**
     * <p>Set a removal time to historic process instances and
     * all associated historic entities using a fluent builder.
     *
     * <p>Historic process instances can be specified by passing a query to
     * SetRemovalTimeToHistoricProcessInstancesBuilder#byQuery(HistoricProcessInstanceQuery).
     *
     * <p>An absolute time can be specified via
     * {@link SetRemovalTimeSelectModeForHistoricProcessInstancesBuilder#absoluteRemovalTime(?string $)}.
     * Pass {@code null} to clear the removal time.
     *
     * <p>As an alternative, the removal time can also be calculated via
     * SetRemovalTimeSelectModeForHistoricProcessInstancesBuilder#calculatedRemovalTime()
     * based on the configured time to live values.
     *
     * <p>To additionally take those historic process instances into account that are part of
     * a hierarchy, enable the flag
     * SetRemovalTimeToHistoricProcessInstancesBuilder#hierarchical()
     *
     * <p>To create the batch and complete the configuration chain, call
     * SetRemovalTimeToHistoricProcessInstancesBuilder#executeAsync().
     */
    public function setRemovalTimeToHistoricProcessInstances(): SetRemovalTimeSelectModeForHistoricProcessInstancesBuilderInterface;

    /**
     * <p>Set a removal time to historic batches and all
     * associated historic entities using a fluent builder.
     *
     * <p>Historic batches can be specified by passing a query to
     * SetRemovalTimeToHistoricBatchesBuilder#byQuery(HistoricBatchQuery).
     *
     * <p>An absolute time can be specified via
     * {@link SetRemovalTimeSelectModeForHistoricBatchesBuilder#absoluteRemovalTime(?string $)}.
     * Pass {@code null} to clear the removal time.
     *
     * <p>As an alternative, the removal time can also be calculated via
     * SetRemovalTimeSelectModeForHistoricBatchesBuilder#calculatedRemovalTime()
     * based on the configured time to live values.
     *
     * <p>To create the batch and complete the configuration chain, call
     * SetRemovalTimeToHistoricBatchesBuilder#executeAsync().
     */
    public function setRemovalTimeToHistoricBatches(): SetRemovalTimeSelectModeForHistoricBatchesBuilderInterface;

    /**
     * <p>Set an annotation to user operation log entries.</p>
     *
     * @throws NotValidException when operation id is {@code null}
     * @throws BadUserRequestException when no user operation could be found
     * @throws AuthorizationException
     * <ul>
     *   <li>
     *     when no ProcessDefinitionPermissions#UPDATE_HISTORY permission
     *     is granted on Resources#PROCESS_DEFINITION</li>
     *   <li>
     *     or when no UserOperationLogCategoryPermissions#UPDATE permission
     *     is granted on Resources#OPERATION_LOG_CATEGORY
     *   </li>
     * </ul>
     *
     * @param operationId of the user operation log entries that are updated
     * @param annotation that is set to the user operation log entries
     */
    public function setAnnotationForOperationLogById(?string $operationId, ?string $annotation): void;

    /**
     * <p>Clear the annotation for user operation log entries.</p>
     *
     * @throws NotValidException when operation id is {@code null}
     * @throws BadUserRequestException when no user operation could be found
     * @throws AuthorizationException
     * <ul>
     *   <li>
     *     when no ProcessDefinitionPermissions#UPDATE_HISTORY permission
     *     is granted on Resources#PROCESS_DEFINITION</li>
     *   <li>
     *     or when no UserOperationLogCategoryPermissions#UPDATE permission
     *     is granted on Resources#OPERATION_LOG_CATEGORY
     *   </li>
     * </ul>
     *
     * @param operationId of the user operation log entries that are updated
     */
    public function clearAnnotationForOperationLogById(?string $operationId): void;
}
