<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Cmd\AbstractSetProcessDefinitionStateCmd;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};

abstract class TimerChangeProcessDefinitionSuspensionStateJobHandler implements JobHandlerInterface
{
    public function execute(ProcessDefinitionSuspensionStateConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $cmd = $this->getCommand($configuration);
        $cmd->disableLogUserOperation();
        $cmd->execute($commandContext);
    }

    abstract protected function getCommand(ProcessDefinitionSuspensionStateConfiguration $configuration): AbstractSetProcessDefinitionStateCmd;

    public function newConfiguration(string $canonicalString): ProcessDefinitionSuspensionStateConfiguration
    {
        $jsonObject = json_decode($canonicalString);

        return ProcessDefinitionSuspensionStateConfiguration::fromJson($jsonObject);
    }

    public function onDelete(ProcessDefinitionSuspensionStateConfiguration $configuration, JobEntity $jobEntity): void
    {
        // do nothing
    }
}
