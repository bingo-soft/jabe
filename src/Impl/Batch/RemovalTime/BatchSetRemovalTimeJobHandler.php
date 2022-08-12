<?php

namespace Jabe\Impl\Batch\RemovalTime;

use Jabe\ProcessEngineConfiguration;
use Jabe\Batch\BatchInterface;
use Jabe\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Impl\Batch\History\HistoricBatchEntity;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\JobDeclaration;
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    MessageEntity
};

class BatchSetRemovalTimeJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function execute(BatchJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId)
    {
        $byteArrayId = $configuration->getConfigurationByteArrayId();
        $configurationByteArray = $this->findByteArrayById($byteArrayId, $commandContext);

        $batchConfiguration = $this->readConfiguration($configurationByteArray);

        foreach ($batchConfiguration->getIds() as $instanceId) {
            $instance = $this->findBatchById($instanceId, $commandContext);

            if ($instance !== null) {
                $removalTime = $this->getOrCalculateRemovalTime($batchConfiguration, $instance, $commandContext);
                if ($removalTime != $instance->getRemovalTime()) {
                    $this->addRemovalTime($instanceId, $removalTime, $commandContext);
                }
            }
        }
    }

    protected function getOrCalculateRemovalTime(SetRemovalTimeBatchConfiguration $batchConfiguration, HistoricBatchEntity $instance, CommandContext $commandContext): string
    {
        if ($batchConfiguration->hasRemovalTime()) {
            return $batchConfiguration->getRemovalTime();
        } elseif ($this->hasBaseTime($instance, $commandContext)) {
            return $this->calculateRemovalTime($instance, $commandContext);
        } else {
            return null;
        }
    }

    protected function addRemovalTime(string $instanceId, string $removalTime, CommandContext $commandContext): void
    {
        $commandContext->getHistoricBatchManager()
            ->addRemovalTimeById($instanceId, $removalTime);
    }

    protected function hasBaseTime(HistoricBatchEntity $instance, CommandContext $commandContext): bool
    {
        return $this->isStrategyStart($commandContext) || ($this->isStrategyEnd($commandContext) && $this->isEnded($instance));
    }

    protected function isEnded(HistoricBatchEntity $instance): bool
    {
        return $instance->getEndTime() !== null;
    }

    protected function isStrategyStart(CommandContext $commandContext): bool
    {
        return ProcessEngineConfiguration::HISTORY_REMOVAL_TIME_STRATEGY_START == $this->getHistoryRemovalTimeStrategy($commandContext);
    }

    protected function isStrategyEnd(CommandContext $commandContext): bool
    {
        return ProcessEngineConfiguration::HISTORY_REMOVAL_TIME_STRATEGY_END == $this->getHistoryRemovalTimeStrategy($commandContext);
    }

    protected function getHistoryRemovalTimeStrategy(CommandContext $commandContext): string
    {
        return $commandContext->getProcessEngineConfiguration()
            ->getHistoryRemovalTimeStrategy();
    }

    /*protected boolean isDmnEnabled(CommandContext commandContext) {
        return commandContext.getProcessEngineConfiguration().isDmnEnabled();
    }*/

    protected function calculateRemovalTime(HistoricBatchEntity $batch, CommandContext $commandContext): string
    {
        return $commandContext->getProcessEngineConfiguration()
            ->getHistoryRemovalTimeProvider()
            ->calculateRemovalTime($batch);
    }

    protected function findByteArrayById(string $byteArrayId, CommandContext $commandContext): ByteArrayEntity
    {
        return $commandContext->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $byteArrayId);
    }

    protected function findBatchById(string $instanceId, CommandContext $commandContext): ?HistoricBatchEntity
    {
        return $commandContext->getHistoricBatchManager()
            ->findHistoricBatchById($instanceId);
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_BATCH_SET_REMOVAL_TIME);
        }
        return self::$JOB_DECLARATION;
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $batchIds): BatchConfiguration
    {
        return (new SetRemovalTimeBatchConfiguration($batchIds))
            ->setRemovalTime($configuration->getRemovalTime())
            ->setHasRemovalTime($configuration->hasRemovalTime());
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return SetRemovalTimeJsonConverter::instance();
    }

    public function getType(): string
    {
        return BatchInterface::TYPE_BATCH_SET_REMOVAL_TIME;
    }
}
