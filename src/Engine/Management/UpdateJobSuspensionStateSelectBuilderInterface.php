<?php

namespace Jabe\Engine\Management;

interface UpdateJobSuspensionStateSelectBuilderInterface
{
    /**
     * Selects the job with the given id.
     *
     * @param jobId
     *          id of the job
     * @return the builder
     */
    public function byJobId(string $jobId): UpdateJobSuspensionStateBuilderInterface;

    /**
     * Selects the jobs of the job definition with the given id.
     *
     * @param jobDefinitionId
     *          id of the job definition
     * @return the builder
     */
    public function byJobDefinitionId(string $jobDefinitionId): UpdateJobSuspensionStateBuilderInterface;

    /**
     * Selects the jobs of the process instance with the given id.
     *
     * @param processInstanceId
     *          id of the process instance
     * @return the builder
     */
    public function byProcessInstanceId(string $processInstanceId): UpdateJobSuspensionStateBuilderInterface;

    /**
     * Selects the jobs of the process definition with the given id.
     *
     * @param processDefinitionId
     *          id of the process definition
     * @return the builder
     */
    public function byProcessDefinitionId(string $processDefinitionId): UpdateJobSuspensionStateBuilderInterface;

    /**
     * Selects the jobs of the process definitions with the given key.
     *
     * @param processDefinitionKey
     *          key of the process definition
     * @return the builder
     */
    public function byProcessDefinitionKey(string $processDefinitionKey): UpdateJobSuspensionStateTenantBuilderInterface;
}
