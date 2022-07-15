<?php

namespace Jabe\Engine\Impl\Batch\RemovalTime;

use Jabe\Engine\ProcessEngineConfiguration;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\JobExecutor\JobDeclaration;
use Jabe\Engine\Impl\Json\JsonObjectConverter;
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    HistoricProcessInstanceEntity,
    MessageEntity
};
use Jabe\Engine\Repository\ProcessDefinitionInterface;

class ProcessSetRemovalTimeJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function execute(BatchJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, string $tenantId = null)
    {
        $byteArrayId = $configuration->getConfigurationByteArrayId();
        $configurationByteArray = $this->findByteArrayById($byteArrayId, $commandContext);

        $batchConfiguration = $this->readConfiguration($configurationByteArray);

        foreach ($batchConfiguration->getIds() as $instanceId) {
            $instance = $this->findProcessInstanceById($instanceId, $commandContext);

            if ($instance != null) {
                if ($batchConfiguration->isHierarchical() && $this->hasHierarchy($instance)) {
                    $rootProcessInstanceId = $instance->getRootProcessInstanceId();
                    $rootInstance = $this->findProcessInstanceById($rootProcessInstanceId, $commandContext);
                    $removalTime = $this->getOrCalculateRemovalTime($batchConfiguration, $rootInstance, $commandContext);
                    $this->addRemovalTimeToHierarchy($rootProcessInstanceId, $removalTime, $commandContext);
                } else {
                    $removalTime = $this->getOrCalculateRemovalTime($batchConfiguration, $instance, $commandContext);
                    if ($removalTime != $instance->getRemovalTime()) {
                        $this->addRemovalTime($instanceId, $removalTime, $commandContext);
                    }
                }
            }
        }
    }

    protected function getOrCalculateRemovalTime(SetRemovalTimeBatchConfiguration $batchConfiguration, HistoricProcessInstanceEntity $instance, CommandContext $commandContext): ?string
    {
        if ($batchConfiguration->hasRemovalTime()) {
            return $batchConfiguration->getRemovalTime();
        } elseif ($this->hasBaseTime($instance, $commandContext)) {
            return $this->calculateRemovalTime($instance, $commandContext);
        } else {
            return null;
        }
    }

    protected function addRemovalTimeToHierarchy(string $rootProcessInstanceId, string $removalTime, CommandContext $commandContext): void
    {
        $commandContext->getHistoricProcessInstanceManager()
            ->addRemovalTimeToProcessInstancesByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        /*if (isDmnEnabled(commandContext)) {
            $commandContext->getHistoricDecisionInstanceManager()
            .addRemovalTimeToDecisionsByRootProcessInstanceId(rootProcessInstanceId, removalTime);
        }*/
    }

    protected function addRemovalTime(string $instanceId, string $removalTime, CommandContext $commandContext): void
    {
        $commandContext->getHistoricProcessInstanceManager()
            ->addRemovalTimeById($instanceId, $removalTime);

        /*if (isDmnEnabled(commandContext)) {
            $commandContext->getHistoricDecisionInstanceManager()
            .addRemovalTimeToDecisionsByProcessInstanceId(instanceId, removalTime);
        }*/
    }

    protected function hasBaseTime(HistoricProcessInstanceEntity $instance, CommandContext $commandContext): bool
    {
        return $this->isStrategyStart($commandContext) || ($this->isStrategyEnd($commandContext) && $this->isEnded($instance));
    }

    protected function isEnded(HistoricProcessInstanceEntity $instance): bool
    {
        return $instance->getEndTime() != null;
    }

    protected function isStrategyStart(CommandContext $commandContext): bool
    {
        return ProcessEngineConfiguration::HISTORY_REMOVAL_TIME_STRATEGY_START == $this->getHistoryRemovalTimeStrategy($commandContext);
    }

    protected function isStrategyEnd(CommandContext $commandContext): bool
    {
        return ProcessEngineConfiguration::HISTORY_REMOVAL_TIME_STRATEGY_END == $this->getHistoryRemovalTimeStrategy($commandContext);
    }

    protected function hasHierarchy(HistoricProcessInstanceEntity $instance): bool
    {
        return $instance->getRootProcessInstanceId() != null;
    }

    protected function getHistoryRemovalTimeStrategy(CommandContext $commandContext): string
    {
        return $commandContext->getProcessEngineConfiguration()
            ->getHistoryRemovalTimeStrategy();
    }

    protected function findProcessDefinitionById(string $processDefinitionId, CommandContext $commandContext): ?ProcessDefinitionInterface
    {
        return $commandContext->getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findDeployedProcessDefinitionById($processDefinitionId);
    }

    /*protected boolean isDmnEnabled(CommandContext commandContext) {
        return $commandContext->getProcessEngineConfiguration().isDmnEnabled();
    }*/

    protected function calculateRemovalTime(HistoricProcessInstanceEntity $processInstance, CommandContext $commandContext): string
    {
        $processDefinition = $this->findProcessDefinitionById($processInstance->getProcessDefinitionId(), $commandContext);

        return $commandContext->getProcessEngineConfiguration()
            ->getHistoryRemovalTimeProvider()
            ->calculateRemovalTime($processInstance, $processDefinition);
    }

    protected function findByteArrayById(string $byteArrayId, CommandContext $commandContext): ByteArrayEntity
    {
        return $commandContext->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $byteArrayId);
    }

    protected function findProcessInstanceById(string $instanceId, CommandContext $commandContext): ?HistoricProcessInstanceEntity
    {
        return $commandContext->getHistoricProcessInstanceManager()
            ->findHistoricProcessInstance($instanceId);
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_PROCESS_SET_REMOVAL_TIME);
        }
        return self::$JOB_DECLARATION;
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $processInstanceIds): BatchConfiguration
    {
        return (new SetRemovalTimeBatchConfiguration($processInstanceIds))
            ->setRemovalTime($configuration->getRemovalTime())
            ->setHasRemovalTime($configuration->hasRemovalTime())
            ->setHierarchical($configuration->isHierarchical());
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return SetRemovalTimeJsonConverter::instance();
    }

    public function getType(): string
    {
        return BatchInteface::TYPE_PROCESS_SET_REMOVAL_TIME;
    }
}
