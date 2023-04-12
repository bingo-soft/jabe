<?php

namespace Tests\Util;

use Jabe\Delegate\{
    ExecutionListenerInterface,
    DelegateExecutionInterface
};

class TestExecutionListener implements ExecutionListenerInterface
{
    public static $collectedEvents = [];

    public function notify(/*DelegateExecutionInterface*/$execution): void
    {
        $counterKey = $execution->getCurrentActivityId() . "-" . $execution->getEventName();
        self::$collectedEvents[] = $counterKey;
    }

    public static function reset(): void
    {
        self::$collectedEvents = [];
    }
}
