<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Cmd\AbstractSetJobDefinitionStateCmd;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};

abstract class TimerChangeJobDefinitionSuspensionStateJobHandler implements JobHandlerInterface
{
    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $cmd = $this->getCommand($configuration);
        $cmd->disableLogUserOperation();
        $cmd->execute($commandContext);
    }

    abstract protected function getCommand(JobDefinitionSuspensionStateConfiguration $configuration): AbstractSetJobDefinitionStateCmd;

    public function newConfiguration(string $canonicalString): JobHandlerConfigurationInterface
    {
        $jsonObject = json_decode($canonicalString);

        return JobDefinitionSuspensionStateConfiguration::fromJson($jsonObject);
    }

    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void
    {
        // do nothing
    }
}
