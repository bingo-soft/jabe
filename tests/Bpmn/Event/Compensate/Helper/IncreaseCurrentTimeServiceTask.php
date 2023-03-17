<?php

namespace Tests\Bpmn\Event\Compensate\Helper;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};
use Jabe\Impl\Util\ClockUtil;

class IncreaseCurrentTimeServiceTask implements PhpDelegateInterface
{
    public function execute(DelegateExecutionInterface $execution)
    {
        fwrite(STDERR, "*** Hello world from IncreaseCurrentTimeServiceTask.execute method ***\n");
        $currentTime = $execution->getVariable("currentTime");
        $currentTime = $dt->setTimestamp($dt->getTimestamp() + 1);
        ClockUtil::setCurrentTime($currentTime);
        $execution->setVariable("currentTime", $currentTime);
    }
}
