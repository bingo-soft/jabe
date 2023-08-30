<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Core\Variable\Scope\AbstractVariableScope;

abstract class AbstractRemoveVariableCmd extends AbstractVariableCmd
{
    protected $variableNames = [];

    public function __construct(?string $entityId, array $variableNames, bool $isLocal)
    {
        parent::__construct($entityId, $isLocal);
        $this->variableNames = $variableNames;
    }

    protected function executeOperation(AbstractVariableScope $scope): void
    {
        if ($this->isLocal) {
            $scope->removeVariablesLocal($this->variableNames);
        } else {
            $scope->removeVariables($this->variableNames);
        }
    }

    protected function getLogEntryOperation(): ?string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_REMOVE_VARIABLE;
    }
}
