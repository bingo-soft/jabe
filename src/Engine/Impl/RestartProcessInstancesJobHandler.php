<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchConfiguration,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Engine\Impl\Cmd\RestartProcessInstancesCmd;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\JobExecutor\JobDeclaration;
use Jabe\Engine\Impl\Json\JsonObjectConverter;
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    JobEntity,
    MessageEntity,
    ProcessDefinitionEntity
};

class RestartProcessInstancesJobHandler extends AbstractBatchJobHandler
{
    private static $JOB_DECLARATION;

    public function getType(): string
    {
        return BatchInterface::TYPE_PROCESS_INSTANCE_RESTART;
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

    public function execute(BatchJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId)
    {
        $configurationEntity = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $configuration->getConfigurationByteArrayId());

        $batchConfiguration = $this->readConfiguration($configurationEntity->getBytes());

        $processDefinitionId = $batchConfiguration->getProcessDefinitionId();
        $builder = new RestartProcessInstanceBuilderImpl($processDefinitionId);

        $builder->processInstanceIds($batchConfiguration->getIds());

        $builder->setInstructions($batchConfiguration->getInstructions());

        if ($batchConfiguration->isInitialVariables()) {
            $builder->initialSetOfVariables();
        }

        if ($batchConfiguration->isSkipCustomListeners()) {
            $builder->skipCustomListeners();
        }

        if ($batchConfiguration->isWithoutBusinessKey()) {
            $builder->withoutBusinessKey();
        }

        if ($batchConfiguration->isSkipIoMappings()) {
            $builder->skipIoMappings();
        }

        $commandExecutor = $commandContext->getProcessEngineConfiguration()
            ->getCommandExecutorTxRequired();
        $commandContext->executeWithOperationLogPrevented(
            new RestartProcessInstancesCmd($commandExecutor, $builder)
        );
        $commandContext->getByteArrayManager()->delete($configurationEntity);
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_PROCESS_INSTANCE_RESTART);
        }
        return self::$JOB_DECLARATION;
    }

    protected function createJobConfiguration(
        BatchConfiguration $configuration,
        array $processIdsForJob
    ): BatchConfiguration {
        return new RestartProcessInstancesBatchConfiguration(
            $processIdsForJob,
            $configuration->getInstructions(),
            $configuration->getProcessDefinitionId(),
            $configuration->isInitialVariables(),
            $configuration->isSkipCustomListeners(),
            $configuration->isSkipIoMappings(),
            $configuration->isWithoutBusinessKey()
        );
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return RestartProcessInstancesBatchConfigurationJsonConverter::instance();
    }
}
