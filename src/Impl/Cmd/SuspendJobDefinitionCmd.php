<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\JobExecutor\TimerSuspendJobDefinitionHandler;
use Jabe\Impl\Management\{
    UpdateJobDefinitionSuspensionStateBuilderImpl,
    UpdateJobSuspensionStateBuilderImpl
};
use Jabe\Impl\Persistence\Entity\SuspensionState;

class SuspendJobDefinitionCmd extends AbstractSetJobDefinitionStateCmd
{
    public function __construct(UpdateJobDefinitionSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::suspended();
    }

    protected function getDelayedExecutionJobHandlerType(): ?string
    {
        return TimerSuspendJobDefinitionHandler::TYPE;
    }

    protected function getNextCommand(/*UpdateJobSuspensionStateBuilderImpl*/$jobCommandBuilder = null): SuspendJobCmd
    {
        return new SuspendJobCmd($jobCommandBuilder);
    }

    protected function getLogEntryOperation(): ?string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_SUSPEND_JOB_DEFINITION;
    }
}
