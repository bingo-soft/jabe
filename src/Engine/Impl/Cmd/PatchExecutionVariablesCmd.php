<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\PropertyChange;

class PatchExecutionVariablesCmd extends AbstractPatchVariablesCmd
{
    public function __construct(string $executionId, array $modifications, array $deletions, bool $isLocal)
    {
        parent::__construct($executionId, $modifications, $deletions, $isLocal);
    }

    protected function getSetVariableCmd(): SetExecutionVariablesCmd
    {
        return new SetExecutionVariablesCmd($this->entityId, $this->variables, $this->isLocal);
    }

    protected function getRemoveVariableCmd(): RemoveExecutionVariablesCmd
    {
        return new RemoveExecutionVariablesCmd($this->entityId, $this->deletions, $this->isLocal);
    }

    public function logVariableOperation(CommandContext $commandContext): void
    {
        $commandContext->getOperationLogManager()->logVariableOperation(
            $this->getLogEntryOperation(),
            $this->entityId,
            null,
            PropertyChange::emptyChange()
        );
    }
}
