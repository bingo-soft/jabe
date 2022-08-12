<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Management\UpdateJobSuspensionStateBuilderImpl;
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;

class SuspendProcessInstanceCmd extends AbstractSetProcessInstanceStateCmd
{
    public function __construct(UpdateProcessInstanceSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::suspended();
    }

    protected function getNextCommand(UpdateJobSuspensionStateBuilderImpl $jobCommandBuilder): SuspendJobCmd
    {
        return new SuspendJobCmd($jobCommandBuilder);
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_SUSPEND;
    }
}
