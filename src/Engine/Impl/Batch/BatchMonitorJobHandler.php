<?php

namespace Jabe\Engine\Impl\Batch;

use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\JobExecutor\{
    JobHandlerInterface,
    JobHandlerConfigurationInterface
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class BatchMonitorJobHandler implements JobHandlerInterface
{
    public const TYPE = "batch-monitor-job";

    public function getType(): string
    {
        return self::TYPE;
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $batchId = $configuration->getBatchId();
        $batch = $commandContext->getBatchManager()->findBatchById($configuration->getBatchId());
        EnsureUtil::ensureNotNull("Batch with id '" . $this->batchId . "' cannot be found", "batch", $batch);

        $completed = $batch->isCompleted();

        if (!$completed) {
            $batch->createMonitorJob(true);
        } else {
            $batch->delete(false, false);
        }
    }

    public function newConfiguration(string $canonicalString): JobHandlerConfigurationInterface
    {
        return new BatchMonitorJobConfiguration($canonicalString);
    }

    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void
    {
        // do nothing
    }
}
