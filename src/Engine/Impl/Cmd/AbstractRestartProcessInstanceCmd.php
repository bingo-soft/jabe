<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\{
    HistoricProcessInstanceQueryImpl,
    RestartProcessInstanceBuilderImpl
};
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    PropertyChange
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;
use BpmPlatform\Engine\Repository\ProcessDefinitionInterface;

abstract class AbstractRestartProcessInstanceCmd implements CommandInterface
{
    protected $commandExecutor;
    protected $builder;

    public function __construct(CommandExecutorInterface $commandExecutor, RestartProcessInstanceBuilderImpl $builder)
    {
        $this->commandExecutor = $commandExecutor;
        $this->builder = $builder;
    }

    protected function collectProcessInstanceIds(): array
    {
        $collectedProcessInstanceIds = [];

        $processInstanceIds = $builder->getProcessInstanceIds();
        if (!empty($processInstanceIds)) {
            $collectedProcessInstanceIds = $processInstanceIds;
        }

        $historicProcessInstanceQuery = $builder->getHistoricProcessInstanceQuery();
        if ($historicProcessInstanceQuery != null) {
            $collectedProcessInstanceIds = array_merge($collectedProcessInstanceIds, $historicProcessInstanceQuery->listIds());
        }

        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "processInstanceIds", $collectedProcessInstanceIds);
        return $collectedProcessInstanceIds;
    }

    public function writeUserOperationLog(
        CommandContext $commandContext,
        ProcessDefinitionInterface $processDefinition,
        int $numInstances,
        bool $async
    ): void {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("nrOfInstances", null, $numInstances);
        $propertyChanges[] = new PropertyChange("async", null, $async);

        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_RESTART_PROCESS_INSTANCE,
                null,
                $processDefinition->getId(),
                $processDefinition->getKey(),
                $propertyChanges
            );
    }

    protected function getProcessDefinition(CommandContext $commandContext, string $processDefinitionId): ?ProcessDefinitionEntity
    {
        return $commandContext
            ->getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findDeployedProcessDefinitionById($processDefinitionId);
    }
}
