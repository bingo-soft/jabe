<?php

namespace Jabe\Engine\Impl\Batch\RemovalTime;

use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Impl\UpdateProcessInstancesSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Engine\Impl\Cmd\UpdateProcessInstancesSuspendStateCmd;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\JobExecutor\JobDeclaration;
use Jabe\Engine\Impl\Json\JsonObjectConverter;
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    MessageEntity
};

class UpdateProcessInstancesSuspendStateJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function getType(): string
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

    public function execute(BatchJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, string $tenantId = null)
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
