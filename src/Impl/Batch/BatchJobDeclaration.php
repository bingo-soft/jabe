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

class BatchJobDeclaration extends JobDeclaration
{
    public function __construct(string $jobHandlerType)
    {
        parent::__construct($jobHandlerType);
    }

    protected function resolveExecution(/*BatchJobContext*/$context): ?ExecutionEntity
    {
        return null;
    }

    protected function newJobInstance($context = null): JobEntity
    {
        return new MessageEntity();
    }

    protected function resolveJobHandlerConfiguration(/*BatchJobContext*/$context): JobHandlerConfigurationInterface
    {
        return new BatchJobConfiguration($context->getConfiguration()->getId());
    }

    protected function resolveJobDefinitionId(/*BatchJobContext*/$context): string
    {
        return $context->getBatch()->getBatchJobDefinitionId();
    }

    public function getJobPriorityProvider(): ParameterValueProviderInterface
    {
        $batchJobPriority = Context::getProcessEngineConfiguration()
            ->getBatchJobPriority();
        return new ConstantValueProvider($batchJobPriority);
    }
}
