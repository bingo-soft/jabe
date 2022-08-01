<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Persistence\Entity\SuspensionState;
use Jabe\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;

class ActivateProcessInstanceCmd extends AbstractSetProcessInstanceStateCmd
{
    public function __construct(UpdateProcessInstanceSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::active();
    }

    protected function getNextCommand($jobCommandBuilder = null)
    {
        return new ActivateJobCmd($jobCommandBuilder);
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_ACTIVATE;
    }
}
