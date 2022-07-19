<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmException
};
use Jabe\Engine\Impl\Pvm\Delegate\ActivityBehaviorInterface;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;
use Jabe\Engine\Impl\Util\EnsureUtil;

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
