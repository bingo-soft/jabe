<?php

namespace Jabe\Impl\Batch\Variables;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\Batch\{
    AbstractBatchJobHandler,
    BatchConfiguration,
    BatchJobConfiguration,
    BatchJobContext,
    BatchJobDeclaration
};
use Jabe\Impl\Cmd\SetExecutionVariablesCmd;
use Jabe\Impl\Core\Variable\VariableUtil;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\JobDeclaration;
use Jabe\Impl\Json\JsonObjectConverter;
use Jabe\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    JobEntity,
    MessageEntity
};

class BatchSetVariablesHandler extends AbstractBatchJobHandler
{
    public static $JOB_DECLARATION;

    public function execute(BatchJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId)
    {
        $byteArrayId = $configuration->getConfigurationByteArrayId();
        $byteArray = $this->findByteArrayById($byteArrayId, $commandContext);

        $configurationByteArray = $byteArray->getBytes();
        $batchConfiguration = $this->readConfiguration($configurationByteArray);

        $batchId = $batchConfiguration->getBatchId();
        $variables = VariableUtil::findBatchVariablesSerialized($batchId, $commandContext);

        $processInstanceIds = $batchConfiguration->getIds();

        foreach ($processInstanceIds as $processInstanceId) {
            $commandContext->executeWithOperationLogPrevented(
                new SetExecutionVariablesCmd($processInstanceId, $variables, false, true)
            );
        }

        $commandContext->getByteArrayManager()->delete($byteArray);
    }

    public function getJobDeclaration(): JobDeclaration
    {
        if (self::$JOB_DECLARATION === null) {
            self::$JOB_DECLARATION = new BatchJobDeclaration(BatchInterface::TYPE_SET_VARIABLES);
        }
        return self::$JOB_DECLARATION;
    }

    protected function createJobConfiguration(BatchConfiguration $configuration, array $processIdsForJob): BatchConfiguration
    {
        return new BatchConfiguration($processIdsForJob);
    }

    protected function getJsonConverterInstance(): JsonObjectConverter
    {
        return SetVariablesJsonConverter::instance();
    }

    public function getType(): string
    {
        return BatchInterface::TYPE_SET_VARIABLES;
    }

    protected function postProcessJob(BatchConfiguration $configuration, JobEntity $job, BatchConfiguration $jobConfiguration): void
    {
        // if there is only one process instance to adjust, set its ID to the job so exclusive scheduling is possible
        if (!empty($jobConfiguration->getIds()) && count($jobConfiguration->getIds())) {
            $job->setProcessInstanceId($jobConfiguration->getIds()[0]);
        }
    }

    protected function findByteArrayById(string $byteArrayId, CommandContext $commandContext): ?ByteArrayEntity
    {
        return $commandContext->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $byteArrayId);
    }
}
