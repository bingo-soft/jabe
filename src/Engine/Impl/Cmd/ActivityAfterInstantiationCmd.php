<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Pvm\Process\{
    ProcessDefinitionImpl,
    ScopeImpl,
    TransitionImpl
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class ActivityAfterInstantiationCmd extends AbstractInstantiationCmd
{
    protected $activityId;

    //@TODO. Check invocation arguments ordering
    public function __construct(?string $processInstanceId, string $activityId, ?string $ancestorActivityInstanceId = null)
    {
        if ($processInstanceId !== null) {
            parent::__construct($processInstanceId, $ancestorActivityInstanceId);
        }
        $this->activityId = $activityId;
    }

    protected function getTargetFlowScope(ProcessDefinitionImpl $processDefinition): ScopeImpl
    {
        $transition = $this->findTransition($processDefinition);
        return $transition->getDestination()->getFlowScope();
    }

    protected function getTargetElement(ProcessDefinitionImpl $processDefinition): CoreModelElement
    {
        return $this->findTransition($processDefinition);
    }

    protected function findTransition(ProcessDefinitionImpl $processDefinition): TransitionImpl
    {
        $activity = $processDefinition->findActivity($this->activityId);

        EnsureUtil::ensureNotNull(
            $this->describeFailure("Activity '" . $this->activityId . "' does not exist"),
            "activity",
            $activity
        );

        if (empty($activity->getOutgoingTransitions())) {
            throw new ProcessEngineException("Cannot start after activity " . $this->activityId . "; activity "
                . "has no outgoing sequence flow to take");
        } elseif (count($activity->getOutgoingTransitions()) > 1) {
            throw new ProcessEngineException("Cannot start after activity " . $this->activityId . "; "
                . "activity has more than one outgoing sequence flow");
        }

        return $activity->getOutgoingTransitions()[0];
    }

    public function getTargetElementId(): string
    {
        return $this->activityId;
    }

    protected function describe(): string
    {
        $sb = "";
        $sb .= "Start after activity '";
        $sb .= $this->activityId;
        $sb .= "'";
        if ($this->ancestorActivityInstanceId !== null) {
            $sb .= " with ancestor activity instance '";
            $sb .= $this->ancestorActivityInstanceId;
            $sb .= "'";
        }

        return $sb;
    }
}
