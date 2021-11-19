<?php

namespace BpmPlatform\Engine\Batch;

interface BatchInterface
{
    public const TYPE_PROCESS_INSTANCE_MIGRATION = "instance-migration";
    public const TYPE_PROCESS_INSTANCE_MODIFICATION = "instance-modification";
    public const TYPE_PROCESS_INSTANCE_RESTART = "instance-restart";
    public const TYPE_PROCESS_INSTANCE_DELETION = "instance-deletion";
    public const TYPE_PROCESS_INSTANCE_UPDATE_SUSPENSION_STATE = "instance-update-suspension-state";
    public const TYPE_HISTORIC_PROCESS_INSTANCE_DELETION = "historic-instance-deletion";
    public const TYPE_HISTORIC_DECISION_INSTANCE_DELETION = "historic-decision-instance-deletion";
    public const TYPE_SET_JOB_RETRIES = "set-job-retries";
    public const TYPE_SET_EXTERNAL_TASK_RETRIES = "set-external-task-retries";
    public const TYPE_PROCESS_SET_REMOVAL_TIME = "process-set-removal-time";
    public const TYPE_DECISION_SET_REMOVAL_TIME = "decision-set-removal-time";
    public const TYPE_BATCH_SET_REMOVAL_TIME = "batch-set-removal-time";
    public const TYPE_SET_VARIABLES = "set-variables";

    /**
     * @return the id of the batch
     */
    public function getId(): string;

    /**
     * @return the type of the batch
     */
    public function getType(): string;

    /**
     * @return the number of batch execution jobs required to complete the batch
     */
    public function getTotalJobs(): int;

    /**
     * @return the number of batch execution jobs already created by the seed job
     */
    public function getJobsCreated(): int;

    /**
     * @return number of batch jobs created per batch seed job invocation
     */
    public function getBatchJobsPerSeed(): int;

    /**
     * @return the number of invocations executed per batch job
     */
    public function getInvocationsPerBatchJob(): int;

    /**
     * @return the id of the batch seed job definition
     */
    public function getSeedJobDefinitionId(): string;

    /**
     * @return the id of the batch monitor job definition
     */
    public function getMonitorJobDefinitionId(): string;

    /**
     * @return the id of the batch job definition
     */
    public function getBatchJobDefinitionId(): string;

    /**
     * @return the batch's tenant id or null
     */
    public function getTenantId(): ?string;

    /**
     * @return the batch creator's user id
     */
    public function getCreateUserId(): string;

    /**
     * <p>
     * Indicates whether this batch is suspended. If a batch is suspended,
     * the batch jobs will not be acquired by the job executor.
     * </p>
     * <p>
     * <p>
     * <strong>Note:</strong> It is still possible to manually suspend and activate
     * jobs and job definitions using the {@link ManagementService}, which will
     * not change the suspension state of the batch.
     * </p>
     *
     * @return true if this batch is currently suspended, false otherwise
     * @see ManagementService#suspendBatchById(String)
     * @see ManagementService#activateBatchById(String)
     */
    public function isSuspended(): bool;
}
