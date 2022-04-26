<?php

namespace Jabe\Engine\Delegate;

use Jabe\Engine\Runtime\IncidentInterface;

interface DelegateExecutionInterface extends BaseDelegateExecutionInterface, BpmnModelExecutionContextInterface, ProcessEngineServicesAwareInterface
{
    /** Reference to the overall process instance */
    public function getProcessInstanceId(): string;

    /**
     * The business key for the process instance this execution is associated
     * with.
     */
    public function getProcessBusinessKey(): ?string;

    /**
     * Configure a business key on the process instance this execution is associated
     * with.
     *
     * @param businessKey the new business key
     */
    public function setProcessBusinessKey(string $businessKey): void;

    /**
     * The process definition key for the process instance this execution is
     * associated with.
     */
    public function getProcessDefinitionId(): string;

    /**
     * Gets the id of the parent of this execution. If null, the execution
     * represents a process-instance.
     */
    public function getParentId(): ?string;

    /**
     * Gets the id of the current activity.
     */
    public function getCurrentActivityId(): string;

    /**
     * Gets the name of the current activity.
     */
    public function getCurrentActivityName(): ?string;

    /**
     * return the Id of the activity instance currently executed by this execution
     */
    public function getActivityInstanceId(): string;

    /**
     * return the Id of the parent activity instance currently executed by this
     * execution
     */
    public function getParentActivityInstanceId(): ?string;

    /** return the Id of the current transition */
    public function getCurrentTransitionId(): string;

    /**
     * Return the process instance execution for this execution. In case this
     * execution is the process instance execution the method returns itself.
     */
    public function getProcessInstance(): DelegateExecutionInterface;

    /**
     * In case this delegate execution is the process instance execution
     * and this process instance was started by a call activity, this method
     * returns the execution which executed the call activity in the super process instance.
     *
     * @return the super execution or null.
     */
    public function getSuperExecution(): ?DelegateExecutionInterface;

    /**
     * Returns whether this execution has been canceled.
     */
    public function isCanceled(): bool;

    /**
     * Return the id of the tenant this execution belongs to. Can be <code>null</code>
     * if the execution belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * Method to store variable in a specific scope identified by activity ID.
     *
     * @param variableName - name of the variable
     * @param value - value of the variable
     * @param activityId - activity ID which is associated with destination execution,
     *                   if not existing - exception will be thrown
     * @throws ProcessEngineException if scope with specified activity ID is not found
     */
    public function setVariable(string $variableName, $value, string $activityId): void;

    /**
     * Create an incident associated with this execution
     *
     * @param incidentType the type of incident
     * @param configuration
     * @return a new incident
     */
    public function createIncident(string $incidentType, string $configuration, ?string $message): IncidentInterface;

    /**
     * Resolve and remove an incident with given id
     *
     * @param incidentId
     */
    public function resolveIncident(string $incidentId): void;
}
