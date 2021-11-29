<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Helper;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Bpmn\Behavior\BpmnBehaviorLogger;
use BpmPlatform\Engine\Impl\Bpmn\Parser\EscalationEventDefinition;
use BpmPlatform\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmScopeInterface
};
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use BpmPlatform\Engine\Impl\Tree\{
    ActivityExecutionHierarchyWalker,
    ActivityExecutionMappingCollector,
    ActivityExecutionTuple,
    OutputVariablesPropagator,
    ReferenceWalker,
    WalkConditionInterface
};

class EscalationHandler
{
    //private final static BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    public static function propagateEscalation(ActivityExecutionInterface $execution, string $escalationCode): void
    {
        $escalationEventDefinition = self::executeEscalation($execution, $escalationCode);

        if ($escalationEventDefinition == null) {
            //throw LOG.missingBoundaryCatchEventEscalation(execution.getActivity().getId(), escalationCode);
        }
    }

    /**
     * Walks through the activity execution hierarchy, fetches and executes matching escalation catch event
     * @return the escalation event definition if found matching escalation catch event
     */
    public static function executeEscalation(ActivityExecutionInterface $execution, string $escalationCode): EscalationEventDefinition
    {
        $currentActivity = $execution->getActivity();

        $escalationEventDefinitionFinder = new EscalationEventDefinitionFinder($escalationCode, $currentActivity);
        $activityExecutionMappingCollector = new ActivityExecutionMappingCollector($execution);

        $walker = new ActivityExecutionHierarchyWalker($execution);
        $walker->addScopePreVisitor($escalationEventDefinitionFinder);
        $walker->addExecutionPreVisitor($activityExecutionMappingCollector);
        $walker->addExecutionPreVisitor(new OutputVariablesPropagator());

        $walker->walkUntil(new class ($escalationEventDefinitionFinder) implements WalkConditionInterface {
            private $escalationEventDefinitionFinder;

            public function __construct(EscalationEventDefinitionFinder $escalationEventDefinitionFinder)
            {
                $this->escalationEventDefinitionFinder = $escalationEventDefinitionFinder;
            }

            public function isFulfilled(ActivityExecutionTuple $element): bool
            {
                return $this->escalationEventDefinitionFinder->getEscalationEventDefinition() != null || $element == null;
            }
        });

        $escalationEventDefinition = $escalationEventDefinitionFinder->getEscalationEventDefinition();
        if ($escalationEventDefinition != null) {
            self::executeEscalationHandler($escalationEventDefinition, $activityExecutionMappingCollector, $escalationCode);
        }
        return $escalationEventDefinition;
    }

    protected static function executeEscalationHandler(
        EscalationEventDefinition $escalationEventDefinition,
        ActivityExecutionMappingCollector $activityExecutionMappingCollector,
        string $escalationCode
    ): void {
        $escalationHandler = $escalationEventDefinition->getEscalationHandler();
        $escalationScope = self::getScopeForEscalation($escalationEventDefinition);
        $escalationExecution = $activityExecutionMappingCollector->getExecutionForScope($escalationScope);

        if ($escalationEventDefinition->getEscalationCodeVariable() != null) {
            $escalationExecution->setVariable($escalationEventDefinition->getEscalationCodeVariable(), $escalationCode);
        }

        $escalationExecution->executeActivity($escalationHandler);
    }

    protected static function getScopeForEscalation(EscalationEventDefinition $escalationEventDefinition): PvmScopeInterface
    {
        $escalationHandler = $escalationEventDefinition->getEscalationHandler();
        if ($escalationEventDefinition->isCancelActivity()) {
            return $escalationHandler->getEventScope();
        } else {
            return $escalationHandler->getFlowScope();
        }
    }
}
