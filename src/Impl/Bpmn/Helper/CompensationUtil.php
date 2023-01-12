<?php

namespace Jabe\Impl\Bpmn\Helper;

use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Event\EventType;
use Jabe\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity
};
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};
use Jabe\Impl\Tree\{
    TreeVisitorInterface,
    FlowScopeWalker,
    ReferenceWalker,
    WalkConditionInterface
};

class CompensationUtil
{
    /**
     * name of the signal that is thrown when a compensation handler completed
     */
    public const SIGNAL_COMPENSATION_DONE = "compensationDone";

    /**
     * we create a separate execution for each compensation handler invocation.
     */
    public static function throwCompensationEvent(array $eventSubscriptions, ActivityExecutionInterface $execution, bool $async): void
    {
        // first spawn the compensating executions
        foreach ($eventSubscriptions as $eventSubscription) {
            // check whether compensating execution is already created
            // (which is the case when compensating an embedded subprocess,
            // where the compensating execution is created when leaving the subprocess
            // and holds snapshot data).
            $compensatingExecution = self::getCompensatingExecution($eventSubscription);
            if ($compensatingExecution !== null) {
                if ($compensatingExecution->getParent() != $execution) {
                    // move the compensating execution under this execution if this is not the case yet
                    $compensatingExecution->setParent($execution);
                }

                $compensatingExecution->setEventScope(false);
            } else {
                $compensatingExecution = $execution->createExecution();
                $eventSubscription->setConfiguration($compensatingExecution->getId());
            }
            $compensatingExecution->setConcurrent(true);
        }

        usort($this->eventSubscriptions, function (EventSubscriptionEntity $o1, EventSubscriptionEntity $o2) {
            return $o2->getCreated() < $o1->getCreated();
        });

        foreach ($this->eventSubscriptions as $compensateEventSubscriptionEntity) {
            $compensateEventSubscriptionEntity->eventReceived(null, $async);
        }
    }

    /**
     * creates an event scope for the given execution:
     *
     * create a new event scope execution under the parent of the given execution
     * and move all event subscriptions to that execution.
     *
     * this allows us to "remember" the event subscriptions after finishing a
     * scope
     */
    public static function createEventScopeExecution(ExecutionEntity $execution): void
    {
        // parent execution is a subprocess or a miBody
        $activity = $execution->getActivity();
        $scopeExecution = $execution->findExecutionForFlowScope($activity->getFlowScope());

        $eventSubscriptions = $execution->getCompensateEventSubscriptions();

        if (count($eventSubscriptions) > 0 || self::hasCompensationEventSubprocess($activity)) {
            $eventScopeExecution = $scopeExecution->createExecution();
            $eventScopeExecution->setActivity($execution->getActivity());
            $eventScopeExecution->activityInstanceStarting();
            $eventScopeExecution->enterActivityInstance();
            $eventScopeExecution->setActive(false);
            $eventScopeExecution->setConcurrent(false);
            $eventScopeExecution->setEventScope(true);

            // copy local variables to eventScopeExecution by value. This way,
            // the eventScopeExecution references a 'snapshot' of the local variables
            $variables = $execution->getVariablesLocal();
            foreach ($variables as $key => $value) {
                $eventScopeExecution->setVariableLocal($key, $value);
            }

            // set event subscriptions to the event scope execution:
            foreach ($eventSubscriptions as $eventSubscriptionEntity) {
                $newSubscription = EventSubscriptionEntity::createAndInsert(
                    $eventScopeExecution,
                    EventType::COMPENSATE,
                    $eventSubscriptionEntity->getActivity()
                );
                $newSubscription->setConfiguration($eventSubscriptionEntity->getConfiguration());
                // use the original date
                $newSubscription->setCreated($eventSubscriptionEntity->getCreated());
            }

            // set existing event scope executions as children of new event scope execution
            // (ensuring they don't get removed when 'execution' gets removed)
            foreach ($execution->getEventScopeExecutions() as $childEventScopeExecution) {
                $childEventScopeExecution->setParent($eventScopeExecution);
            }

            $compensationHandler = self::getEventScopeCompensationHandler($execution);
            $eventSubscription = EventSubscriptionEntity::createAndInsert(
                $scopeExecution,
                EventType::COMPENSATE,
                $compensationHandler
            );
            $eventSubscription->setConfiguration($eventScopeExecution->getId());
        }
    }

    protected static function hasCompensationEventSubprocess(ActivityImpl $activity): bool
    {
        $compensationHandler = $activity->findCompensationHandler();
        return $compensationHandler !== null && $compensationHandler->isSubProcessScope() && $compensationHandler->isTriggeredByEvent();
    }

    /**
     * In the context when an event scope execution is created (i.e. a scope such as a subprocess has completed),
     * this method returns the compensation handler activity that is going to be executed when by the event scope execution.
     *
     * This method is not relevant when the scope has a boundary compensation handler.
     */
    protected static function getEventScopeCompensationHandler(ExecutionEntity $execution): ActivityImpl
    {
        $activity = $execution->getActivity();

        $compensationHandler = $activity->findCompensationHandler();
        if ($compensationHandler !== null && $compensationHandler->isSubProcessScope()) {
            // subprocess with inner compensation event subprocess
            return $compensationHandler;
        } else {
            // subprocess without compensation handler or
            // multi instance activity
            return $activity;
        }
    }

    /**
     * Collect all compensate event subscriptions for scope of given execution.
     */
    public static function collectCompensateEventSubscriptionsForScope(ActivityExecutionInterface $execution): array
    {
        $scopeExecutionMapping = $execution->createActivityExecutionMapping();
        $activity = $execution->getActivity();

        // <LEGACY>: different flow scopes may have the same scope execution =>
        // collect subscriptions in a set
        $subscriptions = [];

        $scope = new \stdClass();
        $scope->scopeExecutionMapping = $scopeExecutionMapping;
        $scope->subscriptions = $subscriptions;

        $eventSubscriptionCollector = new class ($scope) implements TreeVisitorInterface {
            private $scope;

            public function __construct($scope)
            {
                $this->scope = $scope;
            }

            public function visit($obj): void
            {
                foreach ($this->scope->scopeExecutionMapping as $map) {
                    if ($map[0] == $obj) {
                        $execution = $map[1];
                        $this->scope->subscriptions = array_merge($this->scope->subscriptions, $execution->getCompensateEventSubscriptions());
                    }
                }
            }
        };

        (new FlowScopeWalker($activity))->addPostVisitor($eventSubscriptionCollector)->walkUntil(new class () implements WalkConditionInterface {
            public function isFulfilled($element = null): bool
            {
                $consumesCompensationProperty = $element->getProperty(BpmnParse::PROPERTYNAME_CONSUMES_COMPENSATION);
                return empty($consumesCompensationProperty) || $consumesCompensationProperty == true;
            }
        });

        return $scope->subscriptions;
    }

    /**
     * Collect all compensate event subscriptions for activity on the scope of
     * given execution.
     */
    public static function collectCompensateEventSubscriptionsForActivity(ActivityExecutionInterface $execution, ?string $activityRef): array
    {
        $eventSubscriptions = self::collectCompensateEventSubscriptionsForScope($execution);
        $subscriptionActivityId = self::getSubscriptionActivityId($execution, $activityRef);

        $eventSubscriptionsForActivity = [];
        foreach ($eventSubscriptions as $subscription) {
            if ($subscriptionActivityId == $subscription->getActivityId()) {
                $eventSubscriptionsForActivity[] = $subscription;
            }
        }
        return $eventSubscriptionsForActivity;
    }

    public static function getCompensatingExecution(EventSubscriptionEntity $eventSubscription): ?ExecutionEntity
    {
        $configuration = $eventSubscription->getConfiguration();
        if ($configuration !== null) {
            return Context::getCommandContext()->getExecutionManager()->findExecutionById($configuration);
        } else {
            return null;
        }
    }

    private static function getSubscriptionActivityId(ActivityExecutionInterface $execution, ?string $activityRef): ?string
    {
        $activityToCompensate = $execution->getProcessDefinition()->findActivity($activityRef);

        if ($activityToCompensate->isMultiInstance()) {
            $flowScope = $activityToCompensate->getFlowScope();
            return $flowScope->getActivityId();
        } else {
            $compensationHandler = $activityToCompensate->findCompensationHandler();
            if ($compensationHandler !== null) {
                return $compensationHandler->getActivityId();
            } else {
                // if activityRef = subprocess and subprocess has no compensation handler
                return $activityRef;
            }
        }
    }
}
