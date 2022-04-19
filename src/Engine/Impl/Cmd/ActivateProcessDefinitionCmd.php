<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\JobExecutor\TimerActivateProcessDefinitionHandler;
use BpmPlatform\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Persistence\Entity\SuspensionState;
use BpmPlatform\Engine\Impl\Repository\UpdateProcessDefinitionSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;

class ActivateProcessDefinitionCmd extends AbstractSetProcessDefinitionStateCmd
{
    public function __construct(UpdateProcessDefinitionSuspensionStateBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    protected function getNewSuspensionState(): SuspensionState
    {
        return SuspensionState::active();
    }

    protected function getDelayedExecutionJobHandlerType(): string
    {
        return TimerActivateProcessDefinitionHandler::TYPE;
    }

    protected function getSetJobDefinitionStateCmd(UpdateJobDefinitionSuspensionStateBuilderImpl $jobDefinitionSuspensionStateBuilder): AbstractSetJobDefinitionStateCmd
    {
        return new ActivateJobDefinitionCmd($jobDefinitionSuspensionStateBuilder);
    }

    protected function getNextCommand($processInstanceCommandBuilder = null)
    {
        return new ActivateProcessInstanceCmd($processInstanceCommandBuilder);
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_ACTIVATE_PROCESS_DEFINITION;
    }
}
