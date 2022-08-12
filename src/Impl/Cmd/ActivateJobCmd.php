<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Management\UpdateJobSuspensionStateBuilderImpl;
use Jabe\Impl\Persistence\Entity\SuspensionState;

class ActivateJobCmd extends AbstractSetJobStateCmd
{
    public function __construct(UpdateJobSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::active();
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_ACTIVATE_JOB;
    }
}
