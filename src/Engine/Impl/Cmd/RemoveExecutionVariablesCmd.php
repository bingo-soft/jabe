<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Core\Variable\Scope\AbstractVariableScope;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    PropertyChange
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class RemoveExecutionVariablesCmd extends AbstractRemoveVariableCmd
{
    public function __construct(string $executionId, array $variableNames, bool $isLocal)
    {
        parent::__construct($executionId, $variableNames, $isLocal);
    }

    protected function getEntity(): ExecutionEntity
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $this->entityId);

        $execution = $this->commandContext
            ->getExecutionManager()
            ->findExecutionById($this->entityId);

            EnsureUtil::ensureNotNull("execution " . $this->entityId . " doesn't exist", "execution", $execution);

        $this->checkRemoveExecutionVariables($execution);

        return $execution;
    }

    protected function getContextExecution(): ExecutionEntity
    {
        return $this->getEntity();
    }

    protected function logVariableOperation(AbstractVariableScope $scope): void
    {
        $execution = $scope;
        $this->commandContext->getOperationLogManager()->logVariableOperation($this->getLogEntryOperation(), $execution->getId(), null, PropertyChange::emptyChange());
    }

    protected function checkRemoveExecutionVariables(ExecutionEntity $execution): void
    {
        foreach ($this->commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstanceVariables($execution);
        }
    }
}
