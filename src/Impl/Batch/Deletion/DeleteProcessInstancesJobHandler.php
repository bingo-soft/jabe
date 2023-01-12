<?php

namespace Jabe\Impl\Batch\Deletion;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\ProcessInstanceQueryImpl;
use Jabe\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchEntity,
    BatchConfiguration,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration,
    BatchElementConfiguration
};
use Jabe\Impl\Cmd\DeleteProcessInstancesCmd;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\{
    JobDeclaration,
    JobHandlerConfigurationInterface
};
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    MessageEntity
};

class DeleteProcessInstancesJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function getType(): ?string
    {
        return BatchInterface::TYPE_PROCESS_INSTANCE_DELETION;
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return DeleteProcessInstanceBatchConfigurationJsonConverter::instance();
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_PROCESS_INSTANCE_DELETION);
        }
        return self::$JOB_DECLARATION;
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $processIdsForJob): BatchConfiguration
    {
        return new DeleteProcessInstanceBatchConfiguration($processIdsForJob, null, $configuration->getDeleteReason(), $configuration->isSkipCustomListeners(), $configuration->isSkipSubprocesses(), $configuration->isFailIfNotExists());
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $configurationEntity = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $configuration->getConfigurationByteArrayId());

        $batchConfiguration = $this->readConfiguration($configurationEntity->getBytes());

        $commandContext->executeWithOperationLogPrevented(
            new DeleteProcessInstancesCmd(
                $batchConfiguration->getIds(),
                $batchConfiguration->getDeleteReason(),
                $batchConfiguration->isSkipCustomListeners(),
                true,
                $batchConfiguration->isSkipSubprocesses(),
                $batchConfiguration->isFailIfNotExists()
            )
        );

        $commandContext->getByteArrayManager()->delete($configurationEntity);
    }

    protected function createJobEntities(BatchEntity $batch, BatchConfiguration $configuration, ?string $deploymentId, array $processIds, int $invocationsPerBatchJob): void
    {
        // handle legacy batch entities (no up-front deployment mapping has been done)
        if ($deploymentId === null && ($configuration->getIdMappings() === null || $configuration->getIdMappings()->isEmpty())) {
            // create deployment mappings for the ids to process
            $elementConfiguration = new BatchElementConfiguration();
            $query = new ProcessInstanceQueryImpl();
            $query->processInstanceIds($configuration->getIds());
            $elementConfiguration->addDeploymentMappings($query->listDeploymentIdMappings(), $configuration->getIds());
            // create jobs by deployment id
            $parent = parent;
            $elementConfiguration->getMappings()->forEach(function ($mapping) use ($parent, $batch, $configuration, $processIds, $invocationsPerBatchJob) {
                $parent->createJobEntities(
                    $batch,
                    $configuration,
                    $mapping->getDeploymentId(),
                    $mapping->getIds($processIds),
                    $invocationsPerBatchJob
                );
            });
        } else {
            parent::createJobEntities($batch, $configuration, $deploymentId, $processIds, $invocationsPerBatchJob);
        }
    }
}
