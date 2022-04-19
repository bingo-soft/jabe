<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\JobExecutor\TimerSuspendJobDefinitionHandler;
use BpmPlatform\Engine\Impl\Management\{
    UpdateJobDefinitionSuspensionStateBuilderImpl,
    UpdateJobSuspensionStateBuilderImpl
};
use BpmPlatform\Engine\Impl\Persistence\Entity\SuspensionState;

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
