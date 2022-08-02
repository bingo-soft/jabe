<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Core\Variable\Scope\AbstractVariableScope;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    PropertyChange
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class SetExecutionVariablesCmd extends AbstractSetVariableCmd
{
    public function __construct(
        string $executionId,
        array $variables,
        bool $isLocal,
        bool $skipPhpSerializationFormatCheck = false
    ) {
        parent::__construct($executionId, $variables, $isLocal, $skipPhpSerializationFormatCheck);
    }

    protected function getEntity(): ExecutionEntity
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $this->entityId);

        $execution = $this->commandContext
            ->getExecutionManager()
            ->findExecutionById($this->entityId);

            EnsureUtil::ensureNotNull("execution " . $this->entityId . " doesn't exist", "execution", $execution);

        $this->checkSetExecutionVariables($execution);

        return $execution;
    }

    protected function getContextExecution(): ExecutionEntity
    {
        return $this->getEntity();
    }

    protected function logVariableOperation(AbstractVariableScope $scope): void
    {
        $execution = $scope;
        $this->commandContext->getOperationLogManager()->logVariableOperation(
            $this->getLogEntryOperation(),
            $execution->getId(),
            null,
            PropertyChange::emptyChange()
        );
    }

    protected function checkSetExecutionVariables(ExecutionEntity $execution): void
    {
        foreach ($this->commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstanceVariables($execution);
        }
    }
}
