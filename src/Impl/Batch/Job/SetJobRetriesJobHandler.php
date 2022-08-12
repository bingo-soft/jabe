<?php

namespace Jabe\Impl\Batch\ExternalTask;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration,
    SetRetriesBatchConfiguration
};
use Jabe\Impl\Cmd\SetJobsRetriesCmd;
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

class SetJobRetriesJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function getType(): string
    {
        return BatchInterface::TYPE_SET_JOB_RETRIES;
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_SET_JOB_RETRIES);
        }
        return self::$JOB_DECLARATION;
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return SetJobRetriesBatchConfigurationJsonConverter::instance();
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $jobIds): BatchConfiguration
    {
        return new SetRetriesBatchConfiguration($jobIds, $configuration->getRetries());
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $configurationEntity = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $configuration->getConfigurationByteArrayId());

        $batchConfiguration = $this->readConfiguration($configurationEntity->getBytes());

        $commandContext->executeWithOperationLogPrevented(
            new SetJobsRetriesCmd($batchConfiguration->getIds(), $batchConfiguration->getRetries())
        );

        $commandContext->getByteArrayManager()->delete($configurationEntity);
    }
}
