<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

abstract class AbstractPatchVariablesCmd implements CommandInterface, \Serializable
{
    protected $entityId;
    protected $variables = [];
    protected $deletions = [];
    protected $isLocal;

    public function __construct(string $entityId, array $variables, array $deletions, bool $isLocal)
    {
        $this->entityId = $entityId;
        $this->variables = $variables;
        $this->deletions = $deletions;
        $this->isLocal = $isLocal;
    }

    public function execute(CommandContext $commandContext)
    {
        $this->getSetVariableCmd()->disableLogUserOperation()->execute($commandContext);
        $this->getRemoveVariableCmd()->disableLogUserOperation()->execute($commandContext);
        $this->logVariableOperation($commandContext);
        return null;
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_MODIFY_VARIABLE;
    }

    abstract protected function getSetVariableCmd(): AbstractSetVariableCmd;

    abstract protected function getRemoveVariableCmd(): AbstractRemoveVariableCmd;

    abstract protected function logVariableOperation(CommandContext $commandContext): void;
}
