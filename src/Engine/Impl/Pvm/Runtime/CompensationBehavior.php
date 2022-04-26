<?php

namespace Jabe\Engine\Impl\Pvm\Runtime;

use Jabe\Engine\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};

class CompensationBehavior
{
    /**
     * With compensation, we have a dedicated scope execution for every handler, even if the handler is not
     * a scope activity; this must be respected when invoking end listeners, etc.
     */
    public static function executesNonScopeCompensationHandler(PvmExecutionImpl $execution): bool
    {
        $activity = $execution->getActivity();

        return $execution->isScope() && $activity != null && $activity->isCompensationHandler() && !$activity->isScope();
    }

    public static function isCompensationThrowing(PvmExecutionImpl $execution): bool
    {
        $currentActivity = $execution->getActivity();
        if ($currentActivity != null) {
            $isCompensationThrowing = $currentActivity->getProperty(BpmnParse::PROPERTYNAME_THROWS_COMPENSATION);
            if ($isCompensationThrowing != null && $isCompensationThrowing) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines whether an execution is responsible for default compensation handling.
     *
     * This is the case if
     * <ul>
     *   <li>the execution has an activity
     *   <li>the execution is a scope
     *   <li>the activity is a scope
     *   <li>the execution has children
     *   <li>the execution does not throw compensation
     * </ul>
     */
    public static function executesDefaultCompensationHandler(PvmExecutionImpl $scopeExecution): bool
    {
        $currentActivity = $scopeExecution->getActivity();

        if ($currentActivity != null) {
            return $scopeExecution->isScope()
                && $currentActivity->isScope()
                && !empty($scopeExecution->getNonEventScopeExecutions())
                && !self::isCompensationThrowing($scopeExecution);
        }
        return false;
    }

    public static function getParentActivityInstanceId(PvmExecutionImpl $execution): ?string
    {
        $activityExecutionMapping = $execution->createActivityExecutionMapping();
        foreach ($activityExecutionMapping as $map) {
            $scopeExecution = $execution->getActivity()->getFlowScope();
            if ($map[0] == $scopeExecution) {
                $parentScopeExecution = $map[1];
                return $parentScopeExecution->getParentActivityInstanceId();
            }
        }
        return null;
    }
}
