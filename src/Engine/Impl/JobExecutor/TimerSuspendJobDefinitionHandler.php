<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Cmd\{
    AbstractSetJobDefinitionStateCmd,
    SuspendJobDefinitionCmd
};

class TimerSuspendJobDefinitionHandler extends TimerChangeJobDefinitionSuspensionStateJobHandler
{
    public const TYPE = "suspend-job-definition";

    public function getType(): string
    {
        return self::TYPE;
    }

    protected function getCommand(JobDefinitionSuspensionStateConfiguration $configuration): AbstractSetJobDefinitionStateCmd
    {
        return new SuspendJobDefinitionCmd($configuration->createBuilder());
    }
}
