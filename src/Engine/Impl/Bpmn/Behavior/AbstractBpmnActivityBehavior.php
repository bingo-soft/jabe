<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Bpmn\Helper\{
    BpmnExceptionHandler,
    CompensationUtil,
    ErrorPropagationException
};
use Jabe\Engine\Impl\Event\EventType;
use Jabe\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity
};
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Engine\Impl\Pvm\Process\ActivityImpl;

class AbstractBpmnActivityBehavior extends FlowNodeActivityBehavior
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;
    /**
     * Subclasses that call leave() will first pass through this method, before
     * the regular FlowNodeActivityBehavior#leave(ActivityExecution) is
     * called.
     */
    public function doLeave(ActivityExecutionInterface $execution): void
    {
        $currentActivity = $execution->getActivity();
        $compensationHandler = $currentActivity->findCompensationHandler();

        // subscription for compensation event subprocess is already created
        if ($compensationHandler !== null && !$this->isCompensationEventSubprocess($compensationHandler)) {
            $this->createCompensateEventSubscription($execution, $compensationHandler);
        }
        parent::doLeave($execution);
    }

    protected function isCompensationEventSubprocess(ActivityImpl $activity): bool
    {
        return $activity->isCompensationHandler() && $activity->isSubProcessScope() && $activity->isTriggeredByEvent();
    }

    protected function createCompensateEventSubscription(ActivityExecutionInterface $execution, ActivityImpl $compensationHandler): void
    {
        // the compensate event subscription is created at subprocess or miBody of the the current activity
        $currentActivity = $execution->getActivity();
        $scopeExecution = $execution->findExecutionForFlowScope($currentActivity->getFlowScope());

        EventSubscriptionEntity::createAndInsert($scopeExecution, EventType::compensate(), $compensationHandler);
    }

    /**
     * Takes an ActivityExecution and an Callable and wraps
     * the call to the Callable with the proper error propagation. This method
     * also makes sure that exceptions not caught by following activities in the
     * process will be thrown and not propagated.
     *
     * @param execution
     * @param toExecute
     * @throws Exception
     */
    protected function executeWithErrorPropagation(ActivityExecutionInterface $execution, callable $toExecute = null): void
    {
        $activityInstanceId = $execution->getActivityInstanceId();
        try {
            if (is_callable($toExecute)) {
                $toExecute();
            }
        } catch (\Exception $ex) {
            if ($activityInstanceId == $execution->getActivityInstanceId()) {
                try {
                    BpmnExceptionHandler::propagateException($execution, $ex);
                } catch (ErrorPropagationException $e) {
                    // exception has been logged by thrower
                    // re-throw the original exception so that it is logged
                    // and set as cause of the failure
                    throw $ex;
                }
            } else {
                throw $ex;
            }
        }
    }

    public function signal(ActivityExecutionInterface $execution, string $signalName, $signalData): void
    {
        if (CompensationUtil::SIGNAL_COMPENSATION_DONE == $signalName) {
            $this->signalCompensationDone($execution);
        } else {
            parent::signal($execution, $signalName, $signalData);
        }
    }

    protected function signalCompensationDone(ActivityExecutionInterface $execution): void
    {
        // default behavior is to join compensating executions and propagate the signal if all executions have compensated

        // only wait for non-event-scope executions cause a compensation event subprocess consume the compensation event and
        // do not have to compensate embedded subprocesses (which are still non-event-scope executions)

        if (empty($execution->getNonEventScopeExecutions())) {
            if ($execution->getParent() !== null) {
                $parent = $execution->getParent();
                $execution->remove();
                $parent->signal(CompensationUtil::SIGNAL_COMPENSATION_DONE, null);
            }
        } else {
            $execution->forceUpdate();
        }
    }
}
