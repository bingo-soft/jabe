<?php

namespace Jabe\Impl\Util;

use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmException
};
use Jabe\Impl\Pvm\Delegate\ActivityBehaviorInterface;
use Jabe\Impl\Pvm\Runtime\PvmExecutionImpl;
use Jabe\Impl\Util\EnsureUtil;

class ActivityBehaviorUtil
{
    public static function getActivityBehavior(/*PvmExecutionImpl*/$execution): ?ActivityBehaviorInterface
    {
        if ($execution instanceof PvmExecutionImpl) {
            $id = $execution->getId();

            $activity = $execution->getActivity();
            EnsureUtil::ensureNotNull("Execution '" . $id . "' has no current activity.", "activity", $activity);

            $behavior = $activity->getActivityBehavior();
            EnsureUtil::ensureNotNull("There is no behavior specified in " . $activity . " for execution '" . $id . "'.", "behavior", $behavior);

            return $behavior;
        }
        return null;
    }
}
