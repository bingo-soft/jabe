<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};

interface JobHandlerInterface
{
    public function getType(): ?string;

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId, ...$args): void;

    public function newConfiguration(?string $canonicalString): JobHandlerConfigurationInterface;

    /**
     * Clean up before job is deleted. Like removing of auxiliary entities specific for this job handler.
     *
     * @param configuration the job handler configuration
     * @param jobEntity the job entity to be deleted
     */
    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void;
}
