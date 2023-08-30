<?php

namespace Jabe\Batch\History;

interface HistoricBatchInterface
{
    /**
    * @return string the id of the batch
    */
    public function getId(): ?string;

    /**
     * @return string the type of the batch
     */
    public function getType(): ?string;

    /**
     * @return int the number of batch execution jobs required to complete the batch
     */
    public function getTotalJobs(): int;

    /**
     * @return number of batch jobs created per batch seed job invocation
     */
    public function getBatchJobsPerSeed(): int;

    /**
     * @return int the number of invocations executed per batch job
     */
    public function getInvocationsPerBatchJob(): int;

    /**
     * @return string the id of the batch seed job definition
     */
    public function getSeedJobDefinitionId(): ?string;

    /**
     * @return string the id of the batch monitor job definition
     */
    public function getMonitorJobDefinitionId(): ?string;

    /**
     * @return string the id of the batch job definition
     */
    public function getBatchJobDefinitionId(): ?string;

    /**
     * @return string the batch's tenant id or null
     */
    public function getTenantId(): ?string;

    /**
     * @return string the batch creator's user id
     */
    public function getCreateUserId(): ?string;

    /**
     * @return string the date the batch was started
     */
    public function getStartTime(): ?string;

    /**
     * @return string the date the batch was completed
     */
    public function getEndTime(): ?string;

    /** The time the historic batch will be removed. */
    public function getRemovalTime(): ?string;
}
