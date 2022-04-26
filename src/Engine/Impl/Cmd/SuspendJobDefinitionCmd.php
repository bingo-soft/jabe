<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\JobExecutor\TimerSuspendJobDefinitionHandler;
use Jabe\Engine\Impl\Management\{
    UpdateJobDefinitionSuspensionStateBuilderImpl,
    UpdateJobSuspensionStateBuilderImpl
};
use Jabe\Engine\Impl\Persistence\Entity\SuspensionState;

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

    protected function getDelayedExecutionJobHandlerType(): string
    {
        return TimerSuspendJobDefinitionHandler::TYPE;
    }

    protected function getNextCommand(UpdateJobSuspensionStateBuilderImpl $jobCommandBuilder): SuspendJobCmd
    {
        return new SuspendJobCmd($jobCommandBuilder);
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_SUSPEND_JOB_DEFINITION;
    }
}
