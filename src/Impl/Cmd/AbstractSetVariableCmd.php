<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Core\Variable\Scope\AbstractVariableScope;

abstract class AbstractSetVariableCmd extends AbstractVariableCmd
{
    protected $variables = [];

    protected $skipSerializationFormatCheck;

    public function __construct(
        ?string $entityId,
        array $variables,
        ?bool $isLocal = false,
        ?bool $skipSerializationFormatCheck = null
    ) {
        parent::__construct($entityId, $isLocal);
        $this->variables = $variables;
        $this->skipSerializationFormatCheck = $skipSerializationFormatCheck;
    }

    protected function executeOperation(AbstractVariableScope $scope): void
    {
        if ($this->isLocal) {
            $scope->setVariablesLocal($this->variables, $this->skipSerializationFormatCheck);
        } else {
            $scope->setVariables($this->variables, $this->skipSerializationFormatCheck);
        }
    }

    protected function getLogEntryOperation(): ?string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_SET_VARIABLE;
    }
}
