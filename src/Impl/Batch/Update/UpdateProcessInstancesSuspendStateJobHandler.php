<?php

namespace Jabe\Impl\Batch\Update;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\UpdateProcessInstancesSuspensionStateBuilderImpl;
use Jabe\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchConfiguration,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Impl\Cmd\UpdateProcessInstancesSuspendStateCmd;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
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

class UpdateProcessInstancesSuspendStateJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function getType(): ?string
    {
        return BatchInterface::TYPE_PROCESS_INSTANCE_UPDATE_SUSPENSION_STATE;
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return UpdateProcessInstancesSuspendStateBatchConfigurationJsonConverter::instance();
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_PROCESS_INSTANCE_UPDATE_SUSPENSION_STATE);
        }
        return self::$JOB_DECLARATION;
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $processIdsForJob): BatchConfiguration
    {
        return new UpdateProcessInstancesSuspendStateBatchConfiguration($processIdsForJob, $configuration->getSuspended());
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $configurationEntity = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $configuration->getConfigurationByteArrayId());

        $batchConfiguration = $this->readConfiguration($configurationEntity->getBytes());

        $commandExecutor = $commandContext->getProcessEngineConfiguration()
            ->getCommandExecutorTxRequired();
        $commandContext->executeWithOperationLogPrevented(
            new UpdateProcessInstancesSuspendStateCmd(
                $commandExecutor,
                new UpdateProcessInstancesSuspensionStateBuilderImpl($batchConfiguration->getIds()),
                $batchConfiguration->getSuspended()
            )
        );
    }
}
