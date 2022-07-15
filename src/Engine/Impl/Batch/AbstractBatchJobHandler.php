<?php

namespace Jabe\Engine\Impl\Batch;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\JobExecutor\{
    JobDeclaration,
    JobHandlerConfigurationInterface
};
use Jabe\Engine\Impl\Json\JsonObjectConverter;
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ByteArrayManager,
    JobEntity,
    JobManager,
    MessageEntity
};
use Jabe\Engine\Impl\Util\JsonUtil;

abstract class AbstractBatchJobHandler implements BatchJobHandlerInterface
{
    abstract public function getJobDeclaration(): JobDeclaration;

    public function createJobs(BatchEntity $batch): bool
    {
        $configuration = $this->readConfiguration($batch->getConfigurationBytes());
        $deploymentId = null;

        $idMappings = $configuration->getIdMappings();
        $deploymentAware = $idMappings != null && !empty($idMappings);

        $ids = $configuration->getIds();

        if ($deploymentAware) {
            $this->sanitizeMappings($idMappings, $ids);
            $mappingToProcess = $idMappings->get(0);
            $ids = $mappingToProcess->getIds($ids);
            $deploymentId = $mappingToProcess->getDeploymentId();
        }

        $batchJobsPerSeed = $batch->getBatchJobsPerSeed();
        $invocationsPerBatchJob = $batch->getInvocationsPerBatchJob();

        $numberOfItemsToProcess = min($invocationsPerBatchJob * $batchJobsPerSeed, count($ids));

        // view of process instances to process
        $processIds = array_slice($ids, 0, $numberOfItemsToProcess);
        $this->createJobEntities($batch, $configuration, $deploymentId, $processIds, $invocationsPerBatchJob);
        if ($deploymentAware) {
            if (empty($ids)) {
                // all ids of the deployment are handled
                $idMappings->remove(0);
            } else {
                $idMappings->get(0)->removeIds($numberOfItemsToProcess);
            }
        }

        // update batch configuration
        $batch->setConfigurationBytes($this->writeConfiguration($configuration));

        return $deploymentAware ? $idMappings->isEmpty() : empty($ids);
    }

    protected function sanitizeMappings(DeploymentMappings $idMappings, array $ids): void
    {
        // for mixed version SeedJob execution, there might be ids that have been processed
        // without updating the mappings, this is corrected here,
        // see https://jira.camunda.com/browse/CAM-11188
        $elementsToRemove = $idMappings->getOverallIdCount() - count($ids);
        if ($elementsToRemove > 0) {
            foreach ($idMappings as $key => $deploymentMapping) {
                if ($deploymentMapping->getCount() <= $elementsToRemove) {
                    $idMappings->remove($key);
                    $elementsToRemove -= $deploymentMapping->getCount();
                    if ($elementsToRemove == 0) {
                        break;
                    }
                } else {
                    $deploymentMapping->removeIds($elementsToRemove);
                    break;
                }
            }
        }
    }

    protected function createJobEntities(BatchEntity $batch, BatchConfiguration $configuration, string $deploymentId, array $processIds, int $invocationsPerBatchJob): void
    {
        if (empty($processIds)) {
            return;
        }

        $commandContext = Context::getCommandContext();
        $byteArrayManager = $commandContext->getByteArrayManager();
        $jobManager = $commandContext->getJobManager();

        $createdJobs = 0;
        while (!empty($processIds)) {
            $lastIdIndex = min($invocationsPerBatchJob, count($processIds));
            // view of process instances for this job
            $idsForJob = array_splice($processIds, 0, $lastIdIndex);

            $jobConfiguration = $this->createJobConfiguration($configuration, $idsForJob);

            $jobConfiguration->setBatchId($batch->getId());

            $configurationEntity = $this->saveConfiguration($byteArrayManager, $jobConfiguration);

            $job = $this->createBatchJob($batch, $configurationEntity);
            $job->setDeploymentId($deploymentId);
            $this->postProcessJob($configuration, $job, $jobConfiguration);
            $jobManager->insertAndHintJobExecutor($job);

            $idsForJob = [];
            $createdJobs += 1;
        }

        // update created jobs for batch
        $batch->setJobsCreated($batch->getJobsCreated() + $createdJobs);
    }

    abstract protected function createJobConfiguration(BatchConfiguration $configuration, array $processIdsForJob): BatchConfiguration;

    protected function postProcessJob(BatchConfiguration $configuration, JobEntity $job, BatchConfiguration $jobConfiguration): void
    {
      // do nothing as default
    }

    protected function createBatchJob(BatchEntity $batch, ByteArrayEntity $configuration): JobEntity
    {
        $creationContext = new BatchJobContext($batch, $configuration);
        return $this->getJobDeclaration()->createJobInstance($creationContext);
    }

    public function deleteJobs(BatchEntity $batch): void
    {
        $jobs = Context::getCommandContext()
            ->getJobManager()
            ->findJobsByJobDefinitionId($batch->getBatchJobDefinitionId());

        foreach ($jobs as $job) {
            $job->delete();
        }
    }

    public function newConfiguration(string $canonicalString): JobHandlerConfigurationInterface
    {
        return new BatchJobConfiguration($canonicalString);
    }

    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void
    {
        $byteArrayId = $configuration->getConfigurationByteArrayId();
        if ($byteArrayId != null) {
            Context::getCommandContext()->getByteArrayManager()
                ->deleteByteArrayById($byteArrayId);
        }
    }

    protected function saveConfiguration(ByteArrayManager $byteArrayManager, BatchConfiguration $jobConfiguration): ByteArrayEntity
    {
        $configurationEntity = new ByteArrayEntity();
        $configurationEntity->setBytes($this->writeConfiguration($jobConfiguration));
        $byteArrayManager->insert($configurationEntity);
        return $configurationEntity;
    }

    public function writeConfiguration(BatchConfiguration $configuration): string
    {
        $jsonObject = $this->getJsonConverterInstance()->toJsonObject($configuration);
        return JsonUtil::asBytes($jsonObject);
    }

    public function readConfiguration(string $serializedConfiguration): BatchConfiguration
    {
        return $this->getJsonConverterInstance()->toObject(JsonUtil::asObject($serializedConfiguration));
    }

    abstract protected function getJsonConverterInstance(): JsonObjectConverter;
}
