<?php

namespace Jabe\Impl\Bpmn\Helper;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Behavior\BpmnBehaviorLogger;
use Jabe\Impl\Bpmn\Parser\EscalationEventDefinition;
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmScopeInterface
};
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Impl\Tree\{
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

    public static function propagateEscalation(ActivityExecutionInterface $execution, ?string $escalationCode): void
    {
        $escalationEventDefinition = self::executeEscalation($execution, $escalationCode);

        if ($escalationEventDefinition === null) {
            //throw LOG.missingBoundaryCatchEventEscalation(execution.getActivity().getId(), escalationCode);
        }
    }

    /**
     * Walks through the activity execution hierarchy, fetches and executes matching escalation catch event
     * @return EscalationEventDefinition the escalation event definition if found matching escalation catch event
     */
    public static function executeEscalation(ActivityExecutionInterface $execution, ?string $escalationCode): EscalationEventDefinition
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

            public function isFulfilled($element = null): bool
            {
                return $this->escalationEventDefinitionFinder->getEscalationEventDefinition() !== null || $element === null;
            }
        });

        $escalationEventDefinition = $escalationEventDefinitionFinder->getEscalationEventDefinition();
        if ($escalationEventDefinition !== null) {
            self::executeEscalationHandler($escalationEventDefinition, $activityExecutionMappingCollector, $escalationCode);
        }
        return $escalationEventDefinition;
    }

    protected static function executeEscalationHandler(
        EscalationEventDefinition $escalationEventDefinition,
        ActivityExecutionMappingCollector $activityExecutionMappingCollector,
        ?string $escalationCode
    ): void {
        $escalationHandler = $escalationEventDefinition->getEscalationHandler();
        $escalationScope = self::getScopeForEscalation($escalationEventDefinition);
        $escalationExecution = $activityExecutionMappingCollector->getExecutionForScope($escalationScope);

        if ($escalationEventDefinition->getEscalationCodeVariable() !== null) {
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
