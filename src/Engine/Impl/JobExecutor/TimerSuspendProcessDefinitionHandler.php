<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Cmd\{
    AbstractSetProcessDefinitionStateCmd,
    SuspendProcessDefinitionCmd
};

class TimerSuspendProcessDefinitionHandler extends TimerChangeProcessDefinitionSuspensionStateJobHandler
{
    public const TYPE = "suspend-processdefinition";

    public function getType(): string
    {
        return self::TYPE;
    }

    protected function getCommand(ProcessDefinitionSuspensionStateConfiguration $configuration): AbstractSetProcessDefinitionStateCmd
    {
        return new SuspendProcessDefinitionCmd($configuration->createBuilder());
    }
}
