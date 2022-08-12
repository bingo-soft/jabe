<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Cmd\AbstractSetProcessDefinitionStateCmd;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};

abstract class TimerChangeProcessDefinitionSuspensionStateJobHandler implements JobHandlerInterface
{
    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $cmd = $this->getCommand($configuration);
        $cmd->disableLogUserOperation();
        $cmd->execute($commandContext);
    }

    abstract protected function getCommand(ProcessDefinitionSuspensionStateConfiguration $configuration): AbstractSetProcessDefinitionStateCmd;

    public function newConfiguration(string $canonicalString): JobHandlerConfigurationInterface
    {
        $jsonObject = json_decode($canonicalString);

        return ProcessDefinitionSuspensionStateConfiguration::fromJson($jsonObject);
    }

    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void
    {
        // do nothing
    }
}
