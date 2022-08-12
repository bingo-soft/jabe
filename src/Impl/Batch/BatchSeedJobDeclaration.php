<?php

namespace Jabe\Impl\Batch;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Variable\Mapping\Value\{
    ConstantValueProvider,
    ParameterValueProviderInterface
};
use Jabe\Impl\JobExecutor\{
    JobDeclaration,
    JobHandlerConfigurationInterface
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity,
    MessageEntity
};

class BatchSeedJobDeclaration extends JobDeclaration
{
    public function __construct()
    {
        parent::__construct(BatchSeedJobHandler::TYPE);
    }

    protected function resolveExecution(/*BatchEntity*/$batch): ?ExecutionEntity
    {
        return null;
    }

    protected function newJobInstance(/*BatchEntity*/$batch = null): JobEntity
    {
        return new MessageEntity();
    }

    protected function resolveJobHandlerConfiguration(/*BatchEntity*/$batch): JobHandlerConfiguration
    {
        return new BatchSeedJobConfiguration($batch->getId());
    }

    protected function resolveJobDefinitionId(/*BatchEntity*/$batch): string
    {
        return $batch->getSeedJobDefinitionId();
    }

    public function getJobPriorityProvider(): ParameterValueProviderInterface
    {
        $batchJobPriority = Context::getProcessEngineConfiguration()
            ->getBatchJobPriority();
        return new ConstantValueProvider($batchJobPriority);
    }
}
