<?php

namespace Jabe\Impl\Batch\ExternalTask;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Impl\Cmd\{
    SetExternalTasksRetriesCmd,
    UpdateExternalTaskRetriesBuilderImpl
};
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\{
    JobDeclaration,
    JobHandlerConfigurationInterface
};
use Jabe\Impl\Batch\BatchConfiguration;
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    MessageEntity
};

class SetExternalTaskRetriesJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function getType(): ?string
    {
        return BatchInterface::TYPE_SET_EXTERNAL_TASK_RETRIES;
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_SET_EXTERNAL_TASK_RETRIES);
        }
        return self::$JOB_DECLARATION;
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId, ...$args): void
    {
        $configurationEntity = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $configuration->getConfigurationByteArrayId());

        $batchConfiguration = $this->readConfiguration($configurationEntity->getBytes());

        $commandContext->executeWithOperationLogPrevented(
            new SetExternalTasksRetriesCmd(
                new UpdateExternalTaskRetriesBuilderImpl(
                    $batchConfiguration->getIds(),
                    $batchConfiguration->getRetries()
                )
            )
        );

        $commandContext->getByteArrayManager()->delete($configurationEntity);
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $processIdsForJob): BatchConfiguration
    {
        return new SetRetriesBatchConfiguration($processIdsForJob, $configuration->getRetries());
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return SetExternalTaskRetriesBatchConfigurationJsonConverter::instance();
    }
}
