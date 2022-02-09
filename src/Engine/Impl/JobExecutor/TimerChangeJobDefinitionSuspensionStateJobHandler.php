<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Cmd\AbstractSetJobDefinitionStateCmd;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};

abstract class TimerChangeJobDefinitionSuspensionStateJobHandler implements JobHandlerInterface
{
    public function execute(JobDefinitionSuspensionStateConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $cmd = $this->getCommand($configuration);
        $cmd->disableLogUserOperation();
        $cmd->execute($commandContext);
    }

    abstract protected function getCommand(JobDefinitionSuspensionStateConfiguration $configuration): AbstractSetJobDefinitionStateCmd;

    public function newConfiguration(string $canonicalString): JobDefinitionSuspensionStateConfiguration
    {
        $jsonObject = json_decode($canonicalString);

        return JobDefinitionSuspensionStateConfiguration::fromJson($jsonObject);
    }

    public function onDelete(JobDefinitionSuspensionStateConfiguration $configuration, JobEntity $jobEntity): void
    {
        // do nothing
    }
}
