<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\{
    ModificationBuilderImpl,
    ProcessInstanceQueryImpl
};
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    PropertyChange
};
use Jabe\Repository\ProcessDefinitionInterface;

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

        $processInstanceIds = $this->builder->getProcessInstanceIds();
        if (!empty($processInstanceIds)) {
            $collectedProcessInstanceIds = $processInstanceIds;
        }

        $processInstanceQuery = $this->builder->getProcessInstanceQuery();
        if ($processInstanceQuery !== null) {
            $collectedProcessInstanceIds = array_merge($collectedProcessInstanceIds, $processInstanceQuery->listIds());
        }

        return $collectedProcessInstanceIds;
    }

    public function writeUserOperationLog(
        CommandContext $commandContext,
        ProcessDefinitionInterface $processDefinition,
        int $numInstances,
        bool $async,
        ?string $annotation
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

    protected function getProcessDefinition(CommandContext $commandContext, ?string $processDefinitionId): ?ProcessDefinitionEntity
    {
        return $commandContext
            ->getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findDeployedProcessDefinitionById($processDefinitionId);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
