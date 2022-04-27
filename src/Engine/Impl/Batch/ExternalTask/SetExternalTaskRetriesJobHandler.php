<?php

namespace Jabe\Engine\Impl\Batch\ExternalTask;

use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Engine\Impl\Cmd\{
    SetExternalTasksRetriesCmd,
    UpdateExternalTaskRetriesBuilderImpl
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\JobExecutor\{
    JobDeclaration,
    JobHandlerConfigurationInterface
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    MessageEntity
};

class SetExternalTaskRetriesJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function getType(): string
    {
        return BatchInterface::TYPE_SET_EXTERNAL_TASK_RETRIES;
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION == null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_SET_EXTERNAL_TASK_RETRIES);
        }
        return self::$JOB_DECLARATION;
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
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

    protected function createJobConfiguration(SetRetriesBatchConfiguration $configuration, array $processIdsForJob): SetRetriesBatchConfiguration
    {
        return new SetRetriesBatchConfiguration($processIdsForJob, $configuration->getRetries());
    }

    protected function getJsonConverterInstance(): SetExternalTaskRetriesBatchConfigurationJsonConverter
    {
        return SetExternalTaskRetriesBatchConfigurationJsonConverter::instance();
    }
}
