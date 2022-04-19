<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\JobExecutor\TimerActivateJobDefinitionHandler;
use BpmPlatform\Engine\Impl\Management\{
    UpdateJobDefinitionSuspensionStateBuilderImpl,
    UpdateJobSuspensionStateBuilderImpl
};
use BpmPlatform\Engine\Impl\Persistence\Entity\SuspensionState;

class ActivateJobDefinitionCmd extends AbstractSetJobDefinitionStateCmd
{
    public function __construct(UpdateJobDefinitionSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::active();
    }

    protected function getDelayedExecutionJobHandlerType(): string
    {
        return TimerActivateJobDefinitionHandler::TYPE;
    }

    protected function getNextCommand($jobCommandBuilder = null)
    {
        return new ActivateJobCmd($jobCommandBuilder);
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_ACTIVATE_JOB_DEFINITION;
    }
}
