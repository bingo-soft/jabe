<?php

namespace Jabe\Engine\Impl\Batch;

use Jabe\Engine\Impl\JobExecutor\{
    JobDeclaration,
    JobHandlerInterface
};

interface BatchJobHandlerInterface extends JobHandlerInterface
{
    /**
     * Converts the configuration of the batch to a byte array.
     *
     * @param configuration the configuration object
     * @return string the serialized configuration
     */
    public function writeConfiguration(BatchConfiguration $configuration): string;

    /**
     * Read the serialized configuration of the batch.
     *
     * @param serializedConfiguration the serialized configuration
     * @return BatchConfiguration the deserialized configuration object
     */
    public function readConfiguration(string $serializedConfiguration): BatchConfiguration;

    /**
     * Get the job declaration for batch jobs.
     *
     * @return JobDeclaration the batch job declaration
     */
    public function getJobDeclaration(): JobDeclaration;

    /**
     * Creates batch jobs for a batch.
     *
     * @param batch the batch to create jobs for
     * @return bool true of no more jobs have to be created for this batch, false otherwise
     */
    public function createJobs(BatchEntity $batch): bool;

    /**
     * Delete all jobs for a batch.
     *
     * @param batch the batch to delete jobs for
     */
    public function deleteJobs(BatchEntity $batch): void;
}
