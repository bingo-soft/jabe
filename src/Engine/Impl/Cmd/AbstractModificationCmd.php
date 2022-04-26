<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\{
    ModificationBuilderImpl,
    ProcessInstanceQueryImpl
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    PropertyChange
};
use Jabe\Engine\Repository\ProcessDefinitionInterface;

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
