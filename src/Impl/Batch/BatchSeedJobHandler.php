<?php

namespace Jabe\Impl\Batch;

use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\{
    JobHandlerInterface,
    JobHandlerConfigurationInterface
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};
use Jabe\Impl\Util\EnsureUtil;

class BatchSeedJobHandler implements JobHandlerInterface
{
    public const TYPE = "batch-seed-job";

    public function getType(): ?string
    {
        return self::TYPE;
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId, ...$args): void
    {
        $batchId = $configuration->getBatchId();
        $batch = $commandContext->getBatchManager()->findBatchById($configuration->getBatchId());
        EnsureUtil::ensureNotNull("Batch with id '" . $this->batchId . "' cannot be found", "batch", $batch);

        $batchJobHandler = $commandContext
            ->getProcessEngineConfiguration()
            ->getBatchHandlers()
            ->get($batch->getType());

        $done = $batchJobHandler->createJobs($batch);

        if (!$done) {
            $batch->createSeedJob();
        } else {
            // create monitor job initially without due date to
            // enable rapid completion of simple batches
            $batch->createMonitorJob(false);
        }
    }

    public function newConfiguration(?string $canonicalString): JobHandlerConfigurationInterface
    {
        return new BatchSeedJobConfiguration($canonicalString);
    }

    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void
    {
        // do nothing
    }
}
