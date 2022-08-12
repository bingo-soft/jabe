<?php

namespace Jabe\Impl\History\Handler;

use Jabe\Batch\BatchInterface;
use Jabe\Delegate\{
    DelegateExecutionInterface,
    DelegateTaskInterface,
    VariableScopeInterface
};
use Jabe\ExternalTask\ExternalTaskInterface;
use Jabe\Impl\History\Event\HistoryEvent;
use Jabe\Impl\Migration\Instance\MigratingActivityInstance;
use Jabe\Impl\OpLog\UserOperationLogContext;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    VariableInstanceEntity
};
use Jabe\Runtime\{
    IncidentInterface,
    JobInterface
};
use Jabe\Task\IdentityLinkInterface;

interface HistoryEventProducerInterface
{
    // Process instances //////////////////////////////////////

    /**
     * Creates the history event fired when a process instances is <strong>created</strong>.
     *
     * @param execution the current execution.
     * @return the history event
     */
    public function createProcessInstanceStartEvt(DelegateExecutionInterface $execution): HistoryEvent;

    /**
     * Creates the history event fired when a process instance is <strong>updated</strong>.
     *
     * @param execution the process instance
     * @return the created history event
     */
    public function createProcessInstanceUpdateEvt(DelegateExecutionInterface $execution): HistoryEvent;

    /**
     * Creates the history event fired when a process instance is <strong>migrated</strong>.
     *
     * @param execution the process instance
     * @return the created history event
     */
    public function createProcessInstanceMigrateEvt(DelegateExecutionInterface $execution): HistoryEvent;

    /**
     * Creates the history event fired when a process instance is <strong>ended</strong>.
     *
     * @param execution the current execution.
     * @return the history event
     */
    public function createProcessInstanceEndEvt(DelegateExecutionInterface $execution): HistoryEvent;

    // Activity instances /////////////////////////////////////

    /**
     * Creates the history event fired when an activity instance is <strong>started</strong>.
     *
     * @param execution the current execution.
     * @return the history event
     */
    public function createActivityInstanceStartEvt(DelegateExecutionInterface $execution): HistoryEvent;

    /**
     * Creates the history event fired when an activity instance is <strong>updated</strong>.
     *
     * @param execution the current execution.
     * @param task the task association that is currently updated. (May be null in case there is not task associated.)
     * @return the history event
     */
    public function createActivityInstanceUpdateEvt(DelegateExecutionInterface $execution, ?DelegateTaskInterface $task = null): HistoryEvent;

    /**
     * Creates the history event which is fired when an activity instance is migrated.
     *
     * @param actInstance the migrated activity instance which contains the new id's
     * @return the created history event
     */
    public function createActivityInstanceMigrateEvt(MigratingActivityInstance $actInstance): HistoryEvent;

    /**
     * Creates the history event fired when an activity instance is <strong>ended</strong>.
     *
     * @param execution the current execution.
     * @return the history event
     */
    public function createActivityInstanceEndEvt(DelegateExecutionInterface $execution): HistoryEvent;

    // Task Instances /////////////////////////////////////////

    /**
     * Creates the history event fired when a task instance is <strong>created</strong>.
     *
     * @param task the task
     * @return the history event
     */
    public function createTaskInstanceCreateEvt(DelegateTaskInterface $task): HistoryEvent;

    /**
     * Creates the history event fired when a task instance is <strong>updated</strong>.
     *
     * @param task the task
     * @return the history event
     */
    public function createTaskInstanceUpdateEvt(DelegateTaskInterface $task): HistoryEvent;

    /**
     * Creates the history event fired when a task instance is <strong>migrated</strong>.
     *
     * @param task the task
     * @return the history event
     */
    public function createTaskInstanceMigrateEvt(DelegateTaskInterface $task): HistoryEvent;

    /**
     * Creates the history event fired when a task instances is <strong>completed</strong>.
     *
     * @param task the task
     * @param deleteReason
     * @return the history event
     */
    public function createTaskInstanceCompleteEvt(DelegateTaskInterface $task, string $deleteReason): HistoryEvent;

    // User Operation Logs ///////////////////////////////

    /**
     * Creates the history event fired whenever an operation has been performed by a user. This is
     * used for logging actions such as creating a new Task, completing a task, canceling a
     * a process instance, ...
     *
     * @param context the UserOperationLogContext providing the needed informations
     * @return a List of HistoryEvents
     */
    public function createUserOperationLogEvents(UserOperationLogContext $context): array;

    // HistoricVariableUpdateEventEntity //////////////////////

    /**
     * Creates the history event fired when a variable is <strong>created</strong>.
     *
     * @param variableInstance the runtime variable instance
     * @param the scope to which the variable is linked
     * @return the history event
     */
    public function createHistoricVariableCreateEvt(VariableInstanceEntity $variableInstance, VariableScopeInterface $sourceVariableScope): HistoryEvent;

    /**
     * Creates the history event fired when a variable is <strong>updated</strong>.
     *
     * @param variableInstance the runtime variable instance
     * @param the scope to which the variable is linked
     * @return the history event
     */
    public function createHistoricVariableUpdateEvt(VariableInstanceEntity $variableInstance, VariableScopeInterface $sourceVariableScope): HistoryEvent;

    /**
     * Creates the history event fired when a variable is <strong>migrated</strong>.
     *
     * @param variableInstance the runtime variable instance
     * @param the scope to which the variable is linked
     * @return the history event
     */
    public function createHistoricVariableMigrateEvt(VariableInstanceEntity $variableInstance): HistoryEvent;

    /**
     * Creates the history event fired when a variable is <strong>deleted</strong>.
     *
     * @param variableInstance
     * @param variableScopeImpl
     * @return the history event
     */
    public function createHistoricVariableDeleteEvt(VariableInstanceEntity $variableInstance, VariableScopeInterface $sourceVariableScope): HistoryEvent;

    // Form properties //////////////////////////////////////////

    /**
     * Creates the history event fired when a form property is <strong>updated</strong>.
     *
     * @param processInstance the id for the process instance
     * @param propertyId the id of the form property
     * @param propertyValue the value of the form property
     * @param taskId
     * @return the history event
     */
    public function createFormPropertyUpdateEvt(ExecutionEntity $execution, string $propertyId, string $propertyValue, string $taskId): HistoryEvent;

    // Incidents //////////////////////////////////////////

    public function createHistoricIncidentCreateEvt(IncidentInterface $incident): HistoryEvent;

    public function createHistoricIncidentResolveEvt(IncidentInterface $incident): HistoryEvent;

    public function createHistoricIncidentDeleteEvt(IncidentInterface $incident): HistoryEvent;

    public function createHistoricIncidentMigrateEvt(IncidentInterface $incident): HistoryEvent;

    public function createHistoricIncidentUpdateEvt(IncidentInterface $incident): HistoryEvent;

    // Job Log ///////////////////////////////////////////

    /**
     * Creates the history event fired when a job has been <strong>created</strong>.
     *
     * @since 7.3
     */
    public function createHistoricJobLogCreateEvt(JobInterface $job): HistoryEvent;

    /**
     * Creates the history event fired when the execution of a job <strong>failed</strong>.
     *
     * @since 7.3
     */
    public function createHistoricJobLogFailedEvt(JobInterface $job, \Throwable $exception): HistoryEvent;

    /**
     * Creates the history event fired when the execution of a job was <strong>successful</strong>.
     *
     * @since 7.3
     */
    public function createHistoricJobLogSuccessfulEvt(JobInterface $job): HistoryEvent;

    /**
     * Creates the history event fired when the a job has been <strong>deleted</strong>.
     *
     * @since 7.3
     */
    public function createHistoricJobLogDeleteEvt(JobInterface $job): HistoryEvent;

    /**
     * Creates the history event fired when the a batch has been <strong>started</strong>.
     *
     * @since 7.5
     */
    public function createBatchStartEvent(BatchInterface $batch): HistoryEvent;

    /**
     * Creates the history event fired when the a batch has been <strong>completed</strong>.
     *
     * @since 7.5
     */
    public function createBatchEndEvent(BatchInterface $batch): HistoryEvent;

    /**
     * Fired when an identity link is added
     * @param identitylink
     * @return
     */
    public function createHistoricIdentityLinkAddEvent(IdentityLinkInterface $identitylink): HistoryEvent;

    /**
     * Fired when an identity links is deleted
     * @param identityLink
     * @return
     */
    public function createHistoricIdentityLinkDeleteEvent(IdentityLinkInterface $identityLink): HistoryEvent;

    /**
     * Creates the history event when an external task has been <strong>created</strong>.
     *
     * @since 7.7
     */
    public function createHistoricExternalTaskLogCreatedEvt(ExternalTaskInterface $task): HistoryEvent;

    /**
     * Creates the history event when the execution of an external task has <strong>failed</strong>.
     *
     * @since 7.7
     */
    public function createHistoricExternalTaskLogFailedEvt(ExternalTaskInterface $task): HistoryEvent;

    /**
     * Creates the history event when the execution of an external task was <strong>successful</strong>.
     *
     * @since 7.7
     */
    public function createHistoricExternalTaskLogSuccessfulEvt(ExternalTaskInterface $task): HistoryEvent;

    /**
     * Creates the history event when an external task has been <strong>deleted</strong>.
     *
     * @since 7.7
     */
    public function createHistoricExternalTaskLogDeletedEvt(ExternalTaskInterface $task): HistoryEvent;
}
