<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\PropertyChange;

class PatchTaskVariablesCmd extends AbstractPatchVariablesCmd
{
    public function __construct(string $taskId, array $modifications, array $deletions, bool $isLocal)
    {
        parent::__construct($taskId, $modifications, $deletions, $isLocal);
    }

    protected function getSetVariableCmd(): AbstractSetVariableCmd
    {
        return new SetTaskVariablesCmd($this->entityId, $this->variables, $this->isLocal);
    }

    protected function getRemoveVariableCmd(): AbstractRemoveVariableCmd
    {
        return new RemoveTaskVariablesCmd($this->entityId, $this->deletions, $this->isLocal);
    }

    public function logVariableOperation(CommandContext $commandContext): void
    {
        $commandContext->getOperationLogManager()->logVariableOperation(
            $this->getLogEntryOperation(),
            null,
            $this->entityId,
            PropertyChange::emptyChange()
        );
    }
}
