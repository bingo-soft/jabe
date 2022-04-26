<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Management\UpdateJobSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Persistence\Entity\SuspensionState;

class SuspendJobCmd extends AbstractSetJobStateCmd
{
    public function __construct(UpdateJobSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::suspended();
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_SUSPEND_JOB;
    }
}
