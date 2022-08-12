<?php

namespace Jabe\Impl\Batch\Message;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\MessageCorrelationBuilderImpl;
use Jabe\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchConfiguration,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Impl\Cmd\CorrelateAllMessageCmd;
use Jabe\Impl\Core\Variable\VariableUtil;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\JobDeclaration;
use Jabe\Impl\Json\MessageCorrelationBatchConfigurationJsonConverter;
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    JobEntity,
    MessageEntity
};
use Jabe\Runtime\MessageCorrelationBuilderInterface;
use Jabe\Variable\Impl\VariableMapImpl;

class MessageCorrelationBatchJobHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function getType(): string
    {
        return BatchInterface::TYPE_CORRELATE_MESSAGE;
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_CORRELATE_MESSAGE);
        }
        return self::$JOB_DECLARATION;
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return MessageCorrelationBatchConfigurationJsonConverter::instance();
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $processIdsForJob): BatchConfiguration
    {
        return new MessageCorrelationBatchConfiguration(
            $processIdsForJob,
            $configuration->getMessageName(),
            $configuration->getBatchId()
        );
    }

    protected function postProcessJob(BatchConfiguration $configuration, JobEntity $job, BatchConfiguration $jobConfiguration): void
    {
        // if there is only one process instance to adjust, set its ID to the job so exclusive scheduling is possible
        if (!empty($jobConfiguration->getIds()) && count($jobConfiguration->getIds()) == 1) {
            $job->setProcessInstanceId($jobConfiguration->getIds()[0]);
        }
    }

    public function execute(BatchJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId)
    {
        $configurationEntity = $commandContext
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $configuration->getConfigurationByteArrayId());

        $batchConfiguration = $this->readConfiguration($configurationEntity->getBytes());
        $batchId = $batchConfiguration->getBatchId();

        $correlationBuilder = new MessageCorrelationBuilderImpl($commandContext, $batchConfiguration->getMessageName());
        $correlationBuilder->executionsOnly();
        $this->setVariables($batchId, $correlationBuilder, $commandContext);
        foreach ($batchConfiguration->getIds() as $id) {
            $correlationBuilder->processInstanceId($id);
            $commandContext->executeWithOperationLogPrevented(new CorrelateAllMessageCmd($correlationBuilder, false, false));
        }

        $commandContext->getByteArrayManager()->delete($configurationEntity);
    }

    protected function setVariables(?string $batchId, MessageCorrelationBuilderInterface $correlationBuilder, CommandContext $commandContext): void
    {
        $variables = null;
        if ($batchId !== null) {
            $variables = VariableUtil::findBatchVariablesSerialized($batchId, $commandContext);
            if (!empty($variables)) {
                $correlationBuilder->setVariables(new VariableMapImpl($variables));
            }
        }
    }
}
