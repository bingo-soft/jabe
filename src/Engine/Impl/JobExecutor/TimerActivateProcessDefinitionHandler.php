<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Cmd\{
    AbstractSetProcessDefinitionStateCmd,
    ActivateProcessDefinitionCmd
};

class TimerActivateProcessDefinitionHandler extends TimerChangeProcessDefinitionSuspensionStateJobHandler
{
    public const TYPE = "activate-processdefinition";

    public function getType(): string
    {
        return self::TYPE;
    }

    protected function getCommand(ProcessDefinitionSuspensionStateConfiguration $configuration): AbstractSetProcessDefinitionStateCmd
    {
        return new ActivateProcessDefinitionCmd($configuration->createBuilder());
    }
}
