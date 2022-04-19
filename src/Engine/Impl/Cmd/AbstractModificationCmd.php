<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\{
    ModificationBuilderImpl,
    ProcessInstanceQueryImpl
};
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    PropertyChange
};
use BpmPlatform\Engine\Repository\ProcessDefinitionInterface;

abstract class AbstractModificationCmd implements CommandInterface
{
    protected $builder;

    public function __construct(ModificationBuilderImpl $modificationBuilderImpl)
    {
        $this->builder = $modificationBuilderImpl;
    }

    protected function collectProcessInstanceIds(): array
    {
        $collectedProcessInstanceIds = [];

        $processInstanceIds = $builder->getProcessInstanceIds();
        if (!empty($processInstanceIds)) {
            $collectedProcessInstanceIds = $processInstanceIds;
        }

        $processInstanceQuery = $builder->getProcessInstanceQuery();
        if ($processInstanceQuery != null) {
            $collectedProcessInstanceIds = array_merge($collectedProcessInstanceIds, $processInstanceQuery->listIds());
        }

        return $collectedProcessInstanceIds;
    }

    public function writeUserOperationLog(
        CommandContext $commandContext,
        ProcessDefinitionInterface $processDefinition,
        int $numInstances,
        bool $async,
        string $annotation
    ): void {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("nrOfInstances", null, $numInstances);

        $propertyChanges[] = new PropertyChange("async", null, $async);

        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_MODIFY_PROCESS_INSTANCE,
                null,
                $processDefinition->getId(),
                $processDefinition->getKey(),
                $propertyChanges,
                $annotation
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
