<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Impl\Pvm\Runtime\LegacyBehavior;

class EventSubProcessActivityBehavior extends SubProcessActivityBehavior
{
    public function complete(ActivityExecutionInterface $scopeExecution): void
    {
        // check whether legacy behavior needs to be performed.
        if (!LegacyBehavior::eventSubprocessComplete($scopeExecution)) {
            // in case legacy behavior is not performed, the event subprocess behaves in the same way as a regular subprocess.
            parent::complete($scopeExecution);
        }
    }

    public function concurrentChildExecutionEnded(ActivityExecutionInterface $scopeExecution, ActivityExecutionInterface $endedExecution): void
    {
        // Check whether legacy behavior needs to be performed.
        // Legacy behavior means that the event subprocess is not a scope and as a result does not
        // join concurrent executions on it's own. Instead it delegates to the the subprocess activity behavior in which it is embedded.
        if (!LegacyBehavior::eventSubprocessConcurrentChildExecutionEnded($scopeExecution, $endedExecution)) {
            // in case legacy behavior is not performed, the event subprocess behaves in the same way as a regular subprocess.
            parent::concurrentChildExecutionEnded($scopeExecution, $endedExecution);
        }
    }
}
