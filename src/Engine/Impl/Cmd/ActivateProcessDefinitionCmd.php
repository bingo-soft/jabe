<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\JobExecutor\TimerActivateProcessDefinitionHandler;
use Jabe\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Persistence\Entity\SuspensionState;
use Jabe\Engine\Impl\Repository\UpdateProcessDefinitionSuspensionStateBuilderImpl;

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
