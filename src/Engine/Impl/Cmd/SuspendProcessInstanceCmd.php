<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Management\UpdateJobSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Persistence\Entity\SuspensionState;
use BpmPlatform\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;

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
