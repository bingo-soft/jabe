<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};

interface JobHandlerInterface
{
    public function getType(): string;

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void;

    public function newConfiguration(string $canonicalString): JobHandlerConfigurationInterface;

    /**
     * Clean up before job is deleted. Like removing of auxiliary entities specific for this job handler.
     *
     * @param configuration the job handler configuration
     * @param jobEntity the job entity to be deleted
     */
    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void;
}
