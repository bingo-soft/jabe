<?php

namespace Jabe\Impl;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchConfiguration,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\{
    JobDeclaration,
    JobHandlerConfigurationInterface
};
use Jabe\Impl\Json\ModificationBatchConfigurationJsonConverter;
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    JobEntity,
    MessageEntity,
    ProcessDefinitionEntity
};

class ModificationBatchJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function __construct()
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_PROCESS_INSTANCE_MODIFICATION);
        }
    }

    public function getType(): ?string
    {
        return BatchInterface::TYPE_PROCESS_INSTANCE_MODIFICATION;
    }

    protected function postProcessJob(BatchConfiguration $configuration, JobEntity $job, BatchConfiguration $jobConfiguration): void
    {
        if ($job->getDeploymentId() === null) {
            $commandContext = Context::getCommandContext();
            $processDefinitionEntity = $commandContext->getProcessEngineConfiguration()->getDeploymentCache()
                ->findDeployedProcessDefinitionById($configuration->getProcessDefinitionId());
            $job->setDeploymentId($processDefinitionEntity->getDeploymentId());
        }
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $configurationEntity = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $configuration->getConfigurationByteArrayId());

        $batchConfiguration = $this->readConfiguration($configurationEntity->getBytes());

        $executionBuilder = $commandContext->getProcessEngineConfiguration()
            ->getRuntimeService()
            ->createModification($batchConfiguration->getProcessDefinitionId())
            ->processInstanceIds($batchConfiguration->getIds());

        $executionBuilder->setInstructions($batchConfiguration->getInstructions());

        if ($batchConfiguration->isSkipCustomListeners()) {
            $executionBuilder->skipCustomListeners();
        }
        if ($batchConfiguration->isSkipIoMappings()) {
            $executionBuilder->skipIoMappings();
        }

        $executionBuilder->execute(false);

        $commandContext->getByteArrayManager()->delete($configurationEntity);
    }

    public function getJobDeclaration(): JobDeclaration
    {
        return self::$JOB_DECLARATION;
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $processIdsForJob): BatchConfiguration
    {
        return new ModificationBatchConfiguration(
            $processIdsForJob,
            $configuration->getProcessDefinitionId(),
            $configuration->getInstructions(),
            $configuration->isSkipCustomListeners(),
            $configuration->isSkipIoMappings()
        );
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return ModificationBatchConfigurationJsonConverter::instance();
    }

    protected function getProcessDefinition(CommandContext $commandContext, ?string $processDefinitionId): ProcessDefinitionEntity
    {
        return $commandContext->getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findDeployedProcessDefinitionById($processDefinitionId);
    }
}
