<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Management\UpdateJobSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Persistence\Entity\SuspensionState;
use Jabe\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;

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
