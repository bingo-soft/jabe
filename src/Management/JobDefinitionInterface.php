<?php

namespace Jabe\Management;

interface JobDefinitionInterface
{
    /**
     * @return string the id of the job definition.
     */
    public function getId(): string;

    /**
     * @return string the id of the ProcessDefinition this job definition is associated with.
     */
    public function getProcessDefinitionId(): string;

    /**
     * @return string the key of the ProcessDefinition this job definition is associated with.
     */
    public function getProcessDefinitionKey(): string;

    /**
     * The Type of a job. Asynchronous continuation, timer, ...
     *
     * @return string the type of a Job.
     */
    public function getJobType(): string;

    /**
     * The configuration of a job definition provides details about the jobs which will be created.
     * For timer jobs this method returns the timer configuration.
     *
     * @return string the configuration of this job definition.
     */
    public function getJobConfiguration(): string;

    /**
     * The Id of the activity (from BPMN 2.0 Xml) this Job Definition is associated with.
     *
     * @return string the activity id for this Job Definition.
     */
    public function getActivityId(): string;


    /**
     * Indicates whether this job definition is suspended. If a job Definition is suspended,
     * No Jobs created form the job definition will be acquired by the job executor.
     *
     * @return bool - true if this Job Definition is currently suspended.
     */
    public function isSuspended(): bool;

    /**
     * <p>Returns the execution priority for jobs of this definition, if it was set using the
     * ManagementService API. When a job is assigned a priority, the job definition's overriding
     * priority (if set) is used instead of the values defined in the BPMN XML.</p>
     *
     * @return int the priority that overrides the default/BPMN XML priority or <code>null</code> if
     *   no overriding priority is set
     */
    public function getOverridingJobPriority(): int;

    /**
     * The id of the tenant this job definition belongs to. Can be <code>null</code>
     * if the definition belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * The id of the deployment this job definition is related to. In a deployment-aware setup,
     * this leads to all jobs of the same definition being executed on the same node.
     */
    public function getDeploymentId(): ?string;
}
