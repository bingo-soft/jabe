<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Cmd\{
    AbstractSetJobDefinitionStateCmd,
    ActivateJobDefinitionCmd
};

class TimerActivateJobDefinitionHandler extends TimerChangeJobDefinitionSuspensionStateJobHandler
{
    public const TYPE = "activate-job-definition";

    public function getType(): ?string
    {
        return self::TYPE;
    }

    protected function getCommand(JobDefinitionSuspensionStateConfiguration $configuration): AbstractSetJobDefinitionStateCmd
    {
        return new ActivateJobDefinitionCmd($configuration->createBuilder());
    }
}
