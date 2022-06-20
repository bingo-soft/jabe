<?php

namespace Jabe\Engine;

use Doctrine\DBAL\Connection;
use Jabe\Engine\Application\{
    ProcessApplicationReferenceInterface,
    ProcessApplicationRegistrationInterface
};
use Jabe\Engine\Authorization\{
    BatchPermissions,
    GroupsInterface,
    Permissions,
    ProcessDefinitionPermissions,
    ProcessInstancePermissions
};
use Jabe\Engine\Batch\{
    BatchInterface,
    BatchQueryInterface,
    BatchStatisticsQueryInterface
};
use Jabe\Engine\History\HistoricProcessInstanceQueryInterface;
use Jabe\Engine\Management\{
    ActivityStatisticsQueryInterface,
    DeploymentStatisticsQueryInterface,
    JobDefinitionInterface,
    JobDefinitionQueryInterface,
    MetricsQueryInterface,
    ProcessDefinitionStatisticsQueryInterface,
    SchemaLogQueryInterface,
    TableMetaData,
    TablePageQueryInterface,
    UpdateJobDefinitionSuspensionStateBuilderInterface,
    UpdateJobDefinitionSuspensionStateSelectBuilderInterface,
    UpdateJobSuspensionStateBuilderInterface,
    UpdateJobSuspensionStateSelectBuilderInterface
};
use Jabe\Engine\Runtime\{
    ExecutionInterface,
    IncidentInterface,
    JobInterface,
    JobQueryInterface,
    ProcessInstanceQueryInterface
};
use Jabe\Engine\Task\TaskInterface;

interface ManagementServiceInterface
{
    /**
     * Activate a deployment for a given ProcessApplication. The effect of this
     * method is twofold:
     * <ol>
     *   <li>The process engine will execute atomic operations within the context of
     *       that ProcessApplication</li>
     *   <li>The job executor will start acquiring jobs from that deployment</li>
     * </ol>
     *
     * @param deploymentId
     *          the Id of the deployment to activate
     * @param reference
     *          the reference to the process application
     * @return a new {@link ProcessApplicationRegistration}
     *
     * @throws AuthorizationException
     *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
     */
    public function registerProcessApplication(string $deploymentId, ProcessApplicationReferenceInterface $reference): ProcessApplicationRegistrationInterface;

    /**
     * Deactivate a deployment for a given ProcessApplication. This removes the association
     * between the process engine and the process application and optionally removes the associated
     * process definitions from the cache.
     *
     * @param deploymentId
     *          the Id of the deployment to deactivate
     * @param removeProcessDefinitionsFromCache
     *          indicates whether the process definitions should be removed from the deployment cache
     *
     * @throws AuthorizationException
     *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
     */
    public function unregisterProcessApplication($deploymentIds, bool $removeProcessDefinitionsFromCache): void;

    /**
     * @return the name of the process application that is currently registered for
     *         the given deployment or 'null' if no process application is
     *         currently registered.
     *
     * @throws AuthorizationException
     *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
     */
    public function getProcessApplicationForDeployment(string $deploymentId): string;

    /**
     * Get the mapping containing {table name, row count} entries of the database schema.
     *
     * @throws AuthorizationException
     *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
     */
    public function getTableCount(): array;

    /**
     * Gets the table name (including any configured prefix) for an entity like {@link Task},
     * {@link Execution} or the like.
     *
     * @throws AuthorizationException
     *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
     */
    public function getTableName(string $entityClass): string;

    /**
    * Gets the metadata (column names, column types, etc.) of a certain table.
    * Returns null when no table exists with the given name.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function getTableMetaData(string $tableName): TableMetaData;

    /**
    * Creates a {@link TablePageQuery} that can be used to fetch {@link TablePage}
    * containing specific sections of table row data.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function createTablePageQuery(): TablePageQueryInterface;

    /**
    * Returns a new JobQuery implementation, that can be used
    * to dynamically query the jobs.
    */
    public function createJobQuery(): JobQueryInterface;

    /**
    * Returns a new {@link JobDefinitionQuery} implementation, that can be used
    * to dynamically query the job definitions.
    */
    public function createJobDefinitionQuery(): JobDefinitionQueryInterface;

    /**
    * Forced synchronous execution of a job (eg. for administration or testing)
    * The job will be executed, even if the process definition and/or the process instance
    * is in suspended state.
    *
    * @param jobId id of the job to execute, cannot be null.
    *
    * @throws ProcessEngineException
    *          When there is no job with the given id.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function executeJob(string $jobId): void;

    /**
    * Delete the job with the provided id.
    *
    * @param jobId id of the job to execute, cannot be null.
    *
    * @throws ProcessEngineException
    *          When there is no job with the given id.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function deleteJob(string $jobId): void;

    /**
    * Activates the {@link JobDefinition} with the given id.
    *
    * <p>Note: for more complex activate commands use {@link #updateJobDefinitionSuspensionState()}.</p>
    *
    * @param activateJobs If true, all the {@link Job}s of the provided job definition
    *                     will be activated too.
    *
    * @param activationDate The date on which the job definition will be activated. If null, the
    *                       job definition is activated immediately.
    *                       Note: The {@link JobExecutor} needs to be active to use this!
    *
    * @throws ProcessEngineException
    *          If the job definition id is equal null.
    * @throws AuthorizationException thrown if the current user does not possess
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    *   If <code>activateJobs</code> is <code>true</code>, the user must further possess one of the following permissions:
    *   <ul>
    *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
    *     <li>{@link Permissions#UPDATE} on any {@link Resources#PROCESS_INSTANCE}</li>
    *   </ul>
    *
    * @see #activateJobById(String)
    * @see #activateJobByJobDefinitionId(String)
    */
    public function activateJobDefinitionById(string $jobDefinitionId, bool $activateJobs = false, string $activationDate = null): void;

    /**
    * <p>Activates all {@link JobDefinition}s of the provided process definition id.</p>
    *
    * <p>Note: for more complex activate commands use {@link #updateJobDefinitionSuspensionState()}.</p>
    *
    * @param activateJobs If true, all the {@link Job}s of the provided job definition
    *                     will be activated too.
    *
    * @param activationDate The date on which the job definition will be activated. If null, the
    *                       job definition is activated immediately.
    *                       Note: The {@link JobExecutor} needs to be active to use this!
    *
    * @throws ProcessEngineException
    *          If the process definition id is equal null.
    * @throws AuthorizationException thrown if the current user does not possess
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    *   If <code>activateJobs</code> is <code>true</code>, the user must further possess one of the following permissions:
    *   <ul>
    *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
    *     <li>{@link Permissions#UPDATE} on any {@link Resources#PROCESS_INSTANCE}</li>
    *   </ul>
    *
    * @see #activateJobByProcessDefinitionId(String)
    */
    public function activateJobDefinitionByProcessDefinitionId(string $processDefinitionId, bool $activateJobs = false, string $activationDate = null): void;

    /**
    * <p>Activates all {@link JobDefinition}s of the provided process definition key.</p>
    *
    * <p>Note: for more complex activate commands use {@link #updateJobDefinitionSuspensionState()}.</p>
    *
    * @param activateJobs If true, all the {@link Job}s of the provided job definition
    *                     will be activated too.
    *
    * @param activationDate The date on which the job definition will be activated. If null, the
    *                       job definition is activated immediately.
    *                       Note: The {@link JobExecutor} needs to be active to use this!
    *
    * @throws ProcessEngineException
    *          If the process definition key is equal null.
    * @throws AuthorizationException thrown if the current user does not possess
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    *   If <code>activateJobs</code> is <code>true</code>, the user must further possess one of the following permissions:
    *   <ul>
    *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
    *     <li>{@link Permissions#UPDATE} on any {@link Resources#PROCESS_INSTANCE}</li>
    *   </ul>
    *
    * @see #activateJobByProcessDefinitionKey(String)
    */
    public function activateJobDefinitionByProcessDefinitionKey(string $processDefinitionKey, bool $activateJobs = false, string $activationDate = null): void;

    /**
    * Suspends the {@link JobDefinition} with the given id.
    *
    * <p>Note: for more complex suspend commands use {@link #updateJobDefinitionSuspensionState()}.</p>
    *
    * @param suspendJobs If true, all the {@link Job}s of the provided job definition
    *                     will be suspended too.
    *
    * @param suspensionDate The date on which the job definition will be suspended. If null, the
    *                       job definition is suspended immediately.
    *                       Note: The {@link JobExecutor} needs to be active to use this!
    *
    * @throws ProcessEngineException
    *          If the job definition id is equal null.
    * @throws AuthorizationException thrown if the current user does not possess
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    *   If <code>suspendJobs</code> is <code>true</code>, the user must further possess one of the following permissions:
    *   <ul>
    *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
    *     <li>{@link Permissions#UPDATE} on any {@link Resources#PROCESS_INSTANCE}</li>
    *   </ul>
    *
    * @see #suspendJobById(String)
    * @see #suspendJobByJobDefinitionId(String)
    */
    public function suspendJobDefinitionById(string $jobDefinitionId, bool $suspendJobs = false, string $suspensionDate = null): void;

    /**
    * Suspends all {@link JobDefinition}s of the provided process definition id.
    *
    * <p>Note: for more complex suspend commands use {@link #updateJobDefinitionSuspensionState()}.</p>
    *
    * @param suspendJobs If true, all the {@link Job}s of the provided job definition
    *                     will be suspended too.
    *
    * @param suspensionDate The date on which the job definition will be suspended. If null, the
    *                       job definition is suspended immediately.
    *                       Note: The {@link JobExecutor} needs to be active to use this!
    *
    * @throws ProcessEngineException
    *          If the process definition id is equal null.
    * @throws AuthorizationException thrown if the current user does not possess
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    *   If <code>suspendJobs</code> is <code>true</code>, the user must further possess one of the following permissions:
    *   <ul>
    *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
    *     <li>{@link Permissions#UPDATE} on any {@link Resources#PROCESS_INSTANCE}</li>
    *   </ul>
    *
    * @see #suspendJobByProcessDefinitionId(String)
    */
    public function suspendJobDefinitionByProcessDefinitionId(string $processDefinitionId, bool $suspendJobs = false, string $suspensionDate = null): void;

    /**
    * Suspends all {@link JobDefinition}s of the provided process definition key.
    *
    * <p>Note: for more complex suspend commands use {@link #updateJobDefinitionSuspensionState()}.</p>
    *
    * @param suspendJobs If true, all the {@link Job}s of the provided job definition
    *                     will be suspended too.
    *
    * @param suspensionDate The date on which the job definition will be suspended. If null, the
    *                       job definition is suspended immediately.
    *                       Note: The {@link JobExecutor} needs to be active to use this!
    *
    * @throws ProcessEngineException
    *          If the process definition key is equal null.
    * @throws AuthorizationException thrown if the current user does not possess
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    *   If <code>suspendJobs</code> is <code>true</code>, the user must further possess one of the following permissions:
    *   <ul>
    *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
    *     <li>{@link Permissions#UPDATE} on any {@link Resources#PROCESS_INSTANCE}</li>
    *   </ul>
    *
    * @see #suspendJobByProcessDefinitionKey(String)
    */
    public function suspendJobDefinitionByProcessDefinitionKey(string $processDefinitionKey, bool $suspendJobs = false, string $suspensionDate = null): void;

    /**
    * <p>Activates the {@link Job} with the given id.</p>
    *
    * <p>Note: for more complex activate commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the job id is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function activateJobById(string $jobId): void;

    /**
    * <p>Activates all {@link Job}s of the provided job definition id.</p>
    *
    * <p>Note: for more complex activate commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the job definition id is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function activateJobByJobDefinitionId(string $jobDefinitionId): void;

    /**
    * <p>Activates all {@link Job}s of the provided process instance id.</p>
    *
    * <p>Note: for more complex activate commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the process instance id is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function activateJobByProcessInstanceId(string $processInstanceId): void;

    /**
    * <p>Activates all {@link Job}s of the provided process definition id.</p>
    *
    * <p>Note: for more complex activate commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the process definition id is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function activateJobByProcessDefinitionId(string $processDefinitionId): void;

    /**
    * <p>Activates {@link Job}s of the provided process definition key.</p>
    *
    * <p>Note: for more complex activate commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the process definition key is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function activateJobByProcessDefinitionKey(string $processDefinitionKey): void;

    /**
    * <p>Suspends the {@link Job} with the given id.</p>
    *
    * <p>Note: for more complex suspend commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the job id is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function suspendJobById(string $jobId): void;

    /**
    * <p>Suspends all {@link Job}s of the provided job definition id.</p>
    *
    * <p>Note: for more complex suspend commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the job definition id is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function suspendJobByJobDefinitionId(string $jobDefinitionId): void;

    /**
    * <p>Suspends all {@link Job}s of the provided process instance id.</p>
    *
    * <p>Note: for more complex suspend commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the process instance id is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function suspendJobByProcessInstanceId(string $processInstanceId): void;

    /**
    * <p>Suspends all {@link Job}s of the provided process definition id.</p>
    *
    * <p>Note: for more complex suspend commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the process definition id is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function suspendJobByProcessDefinitionId(string $processDefinitionId): void;

    /**
    * <p>Suspends {@link Job}s of the provided process definition key.</p>
    *
    * <p>Note: for more complex suspend commands use {@link #updateJobSuspensionState()}.</p>
    *
    * @throws ProcessEngineException
    *          If the process definition key is equal null.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function suspendJobByProcessDefinitionKey(string $processDefinitionKey): void;

    /**
    * Activate or suspend jobs using a fluent builder. Specify the jobs by
    * calling one of the <i>by</i> methods, like <i>byJobId</i>. To update the
    * suspension state call {@link UpdateJobSuspensionStateBuilder#activate()} or
    * {@link UpdateJobSuspensionStateBuilder#suspend()}.
    *
    * @return the builder to update the suspension state
    */
    public function updateJobSuspensionState(): UpdateJobSuspensionStateSelectBuilderInterface;

    /**
    * Activate or suspend job definitions using a fluent builder. Specify the job
    * definitions by calling one of the <i>by</i> methods, like
    * <i>byJobDefinitionId</i>. To update the suspension state call
    * {@link UpdateJobDefinitionSuspensionStateBuilder#activate()} or
    * {@link UpdateJobDefinitionSuspensionStateBuilder#suspend()}.
    *
    * @return the builder to update the suspension state
    */
    public function updateJobDefinitionSuspensionState(): UpdateJobDefinitionSuspensionStateSelectBuilderInterface;

    /**
    * Sets the number of retries that a job has left.
    *
    * Whenever the JobExecutor fails to execute a job, this value is decremented.
    * When it hits zero, the job is supposed to be dead and not retried again.
    * In that case, this method can be used to increase the number of retries.
    *
    * @param jobId id of the job to modify, cannot be null.
    * @param retries number of retries.
    *
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          and no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}
    *          and no {@link ProcessInstancePermissions#RETRY_JOB} permission on {@link Resources#PROCESS_INSTANCE}
    *          and no {@link ProcessDefinitionPermissions#RETRY_JOB} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function setJobRetries($jobIds, int $retries): void;

    /**
    * Sets the number of retries that jobs have left asynchronously.
    *
    * Whenever the JobExecutor fails to execute a job, this value is decremented.
    * When it hits zero, the job is supposed to be dead and not retried again.
    * In that case, this method can be used to increase the number of retries.
    *
    * Either jobIds or jobQuery has to be provided. If both are provided resulting list
    * of affected jobs will contain jobs matching query as well as jobs defined in the list.
    *
    * @param ids jobs or process instance to modify.
    * @param query query that identifies which jobs should be modified or process instances with jobs that have to be modified
    * @param retries number of retries.
    *
    * @throws BadUserRequestException if neither jobIds, nor jobQuery is provided or result in empty list
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#CREATE} or
    *          {@link BatchPermissions#CREATE_BATCH_SET_JOB_RETRIES} permission on {@link Resources#BATCH}.
    */
    public function setJobRetriesAsync($ids, $queryOrRetries, $historicQueryOrRetries = null, $retries = null): BatchInterface;

    /**
    * <p>
    * Set the number of retries of all <strong>failed</strong> {@link Job jobs}
    * of the provided job definition id.
    * </p>
    *
    * <p>
    * Whenever the JobExecutor fails to execute a job, this value is decremented.
    * When it hits zero, the job is supposed to be <strong>failed</strong> and
    * not retried again. In that case, this method can be used to increase the
    * number of retries.
    * </p>
    *
    * <p>
    * {@link Incident Incidents} of the involved failed {@link Job jobs} will not
    * be resolved using this method! When the execution of a job was successful
    * the corresponding incident will be resolved.
    * </p>
    *
    * @param jobDefinitionId id of the job definition, cannot be null.
    * @param retries number of retries.
    *
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          and no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}
    *          and no {@link Permissions#RETRY_JOB} permission on {@link Resources#PROCESS_INSTANCE}
    *          and no {@link Permissions#RETRY_JOB} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function setJobRetriesByJobDefinitionId(string $jobDefinitionId, int $retries): void;

    /**
    * Sets a new due date for the provided id. The offset between
    * the old and the new due date can be cascaded to all follow-up
    * jobs. Cascading only works with timer jobs.
    * When newDuedate is null, the job is executed with the next
    * job executor run. In this case the cascade parameter is ignored.
    *
    * @param jobId id of job to modify, cannot be null.
    * @param newDuedate new date for job execution
    * @param cascade indicate whether follow-up jobs should be affected
    *
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function setJobDuedate(string $jobId, string $newDuedate, bool $cascade = false): void;

    /**
    * Triggers the recalculation for the job with the provided id.
    *
    * @param jobId id of job to recalculate, must neither be null nor empty.
    * @param creationDateBased
    *          indicates whether the recalculation should be based on the
    *          creation date of the job or the current date
    *
    * @throws AuthorizationException
    *           If the user has no {@link Permissions#UPDATE} permission on
    *           {@link Resources#PROCESS_INSTANCE} or no
    *           {@link Permissions#UPDATE_INSTANCE} permission on
    *           {@link Resources#PROCESS_DEFINITION}.
    */
    public function recalculateJobDuedate(string $jobId, bool $creationDateBased): void;

    /**
    * Sets a new priority for the job with the provided id.
    *
    * @param jobId the id of the job to modify, must not be null
    * @param priority the job's new priority
    *
    * @throws AuthorizationException thrown if the current user does not possess any of the following permissions
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
    *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    * @since 7.4
    */
    public function setJobPriority(string $jobId, int $priority): void;

    /**
    * <p>Sets an explicit priority for jobs of the given job definition.
    * Jobs created after invoking this method receive the given priority.
    * This setting overrides any setting specified in the BPMN 2.0 XML.</p>
    *
    * <p>The overriding priority can be cleared by using the method
    * {@link #clearOverridingJobPriorityForJobDefinition(String)}.</p>
    *
    * @param jobDefinitionId the id of the job definition to set the priority for
    * @param priority the priority to set;
    * @param cascade if true, priorities of existing jobs of the given definition are changed as well
    *
    * @throws AuthorizationException thrown if the current user does not possess any of the following permissions
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    * @since 7.4
    */
    public function setOverridingJobPriorityForJobDefinition(string $jobDefinitionId, int $priority, bool $cascade = false): void;

    /**
    * <p>Clears the job definition's overriding job priority if set. After invoking this method,
    * new jobs of the given definition receive the priority as specified in the BPMN 2.0 XML
    * or the global default priority.</p>
    *
    * <p>Existing job instance priorities remain unchanged.</p>
    *
    * @param jobDefinitionId the id of the job definition for which to clear the overriding priority
    *
    * @throws AuthorizationException thrown if the current user does not possess any of the following permissions
    *   <ul>
    *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_DEFINITION}</li>
    *   </ul>
    *
    * @since 7.4
    */
    public function clearOverridingJobPriorityForJobDefinition(string $jobDefinitionId): void;

    /**
    * Returns the full stacktrace of the exception that occurs when the job
    * with the given id was last executed. Returns null when the job has no
    * exception stacktrace.
    *
    * @param jobId id of the job, cannot be null.
    *
    * @throws ProcessEngineException
    *          When no job exists with the given id.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_INSTANCE}
    *          or no {@link Permissions#READ_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function getJobExceptionStacktrace(string $jobId): string;

    /**
    * @return a map of all properties.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function getProperties(): array;

    /**
    * Set the value for a property.
    *
    * @param name the name of the property.
    *
    * @param value the new value for the property.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function setProperty(string $name, string $value): void;

    /**
    * Deletes a property by name. If the property does not exist, the request is ignored.
    *
    * @param name the name of the property to delete
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function deleteProperty(string $name): void;

    /**
    * Set the license key.
    *
    * @param licenseKey the license key string.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function setLicenseKey(string $licenseKey): void;

    /**
    * Get the stored license key string or <code>null</code> if no license is set.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function getLicenseKey(): ?string;

    /**
    * Deletes the stored license key. If no license key is set, the request is ignored.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function deleteLicenseKey(): void;

    /** programmatic schema update on a given connection returning feedback about what happened
    *
    *  Note: will always return an empty string
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function databaseSchemaUpgrade(Connection $connection, string $catalog, string $schema): string;

    /**
    * Query for the number of process instances aggregated by process definitions.
    */
    public function createProcessDefinitionStatisticsQuery(): ProcessDefinitionStatisticsQueryInterface;

    /**
    * Query for the number of process instances aggregated by deployments.
    */
    public function createDeploymentStatisticsQuery(): DeploymentStatisticsQueryInterface;

    /**
    * Query for the number of activity instances aggregated by activities of a single process definition.
    *
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_DEFINITION}.
    */
    public function createActivityStatisticsQuery(string $processDefinitionId): ActivityStatisticsQueryInterface;

    /**
    * Get the deployments that are registered the engine's job executor.
    * This set is only relevant, if the engine configuration property <code>jobExecutorDeploymentAware</code> is set.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function getRegisteredDeployments(): array;

    /**
    * Register a deployment for the engine's job executor.
    * This is required, if the engine configuration property <code>jobExecutorDeploymentAware</code> is set.
    * If set to false, the job executor will execute any job.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function registerDeploymentForJobExecutor(string $deploymentId): void;

    /**
    * Unregister a deployment for the engine's job executor.
    * If the engine configuration property <code>jobExecutorDeploymentAware</code> is set,
    * jobs for the given deployment will no longer get acquired.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function unregisterDeploymentForJobExecutor(string $deploymentId): void;

    /**
    * Get the configured history level for the process engine.
    *
    * @return the history level
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function getHistoryLevel(): int;

    /**
    * @return a new metrics Query.
    * @since 7.3
    */
    public function createMetricsQuery(): MetricsQueryInterface;

    /**
    * Deletes all metrics events which are older than the specified timestamp
    * and reported by the given reporter. If a parameter is null, all metric events
    * are matched in that regard.
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    *
    * @param timestamp or null
    * @param reporter or null
    * @since 7.4
    */
    public function deleteMetrics(string $timestamp = null, string $reporter = null);

    /**
    * Forces this engine to commit its pending collected metrics to the database.
    *
    * @throws ProcessEngineException if metrics reporting is disabled or the db metrics
    * reporter is deactivated
    */
    public function reportDbMetricsNow(): void;

    /**
    * Calculates the number of unique task workers based on user task assignees.
    *
    * @param startTime restrict to data collected after the given date (inclusive), can be <code>null</code>
    * @param endTime restrict to data collected before the given date (exclusive), can be <code>null</code>
    * @return the aggregated number of unique task workers (may be restricted to a certain interval)
    */
    public function getUniqueTaskWorkerCount(?string $startTime = null, ?string $endTime = null): int;

    /**
    * Deletes all task metrics which are older than the specified timestamp.
    * If the timestamp is null, all metrics will be deleted
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    *
    * @param timestamp or <code>null</code>
    */
    public function deleteTaskMetrics(?string $timestamp = null): void;

    /**
    * Creates a query to search for {@link org.camunda.bpm.engine.batch.Batch} instances.
    *
    * @since 7.5
    */
    public function createBatchQuery(): BatchQueryInterface;

    /**
    * <p>
    *   Suspends the {@link Batch} with the given id immediately.
    * </p>
    *
    * <p>
    *   <strong>Note:</strong> All {@link JobDefinition}s and {@link Job}s
    *   related to the provided batch will be suspended.
    * </p>
    *
    * @throws BadUserRequestException
    *          If no such batch can be found.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#BATCH}.
    *
    * @since 7.5
    */
    public function suspendBatchById(string $batchId): void;

    /**
    * <p>
    *   Activates the {@link Batch} with the given id immediately.
    * </p>
    *
    * <p>
    *   <strong>Note:</strong> All {@link JobDefinition}s and {@link Job}s
    *   related to the provided batch will be activated.
    * </p>
    *
    * @throws BadUserRequestException
    *          If no such batch can be found.
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#UPDATE} permission on {@link Resources#BATCH}.
    *
    * @since 7.5
    */
    public function activateBatchById(string $batchId): void;

    /**
    * Deletes a batch instance and the corresponding job definitions.
    *
    * If cascade is set to true the historic batch instances and the
    * historic jobs logs are also removed.
    *
    * @throws AuthorizationException
    *          If the user has no {@link Permissions#DELETE} permission on {@link Resources#BATCH}
    *
    * @since 7.5
    */
    public function deleteBatch(string $batchId, bool $cascade): void;

    /**
    * Query for the statistics of the batch execution jobs of a batch.
    *
    * @since 7.5
    */
    public function createBatchStatisticsQuery(): BatchStatisticsQueryInterface;

    /**
    * Query for entries of the database schema log.
    *
    * @since 7.11
    */
    public function createSchemaLogQuery(): SchemaLogQueryInterface;

    /**
    * Enable/disable sending telemetry data to Camunda
    *
    * @throws AuthorizationException
    *          If the user is not a member of the group {@link Groups#CAMUNDA_ADMIN}.
    */
    public function toggleTelemetry(bool $enabled): void;

    /**
    * Checks how sending telemetry data to Camunda is configured
    * @return
    *   <ul>
    *     <li><code>null</code> if the configuration is not defined so far,
    *     treated as <code>false</code> and no data is sent,</li>
    *     <li><code>true</code> if the telemetry sending is enabled, and</li>
    *     <li><code>false</code> if the telemetry is disabled explicitly.</li>
    *   </ul>
    */
    public function isTelemetryEnabled(): ?bool;
}
