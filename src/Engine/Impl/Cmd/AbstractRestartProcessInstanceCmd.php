<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\{
    HistoricProcessInstanceQueryImpl,
    RestartProcessInstanceBuilderImpl
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    PropertyChange
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Repository\ProcessDefinitionInterface;

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

        $processInstanceIds = $this->builder->getProcessInstanceIds();
        if (!empty($processInstanceIds)) {
            $collectedProcessInstanceIds = $processInstanceIds;
        }

        $historicProcessInstanceQuery = $this->builder->getHistoricProcessInstanceQuery();
        if ($historicProcessInstanceQuery !== null) {
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
