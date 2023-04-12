<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl,
    ScopeImpl
};

class ActivityBeforeInstantiationCmd extends AbstractInstantiationCmd
{
    protected $activityId;

    //@TODO. Check invocation arguments ordering
    public function __construct(?string $processInstanceId, ?string $activityId, ?string $ancestorActivityInstanceId = null)
    {
        if ($processInstanceId !== null) {
            parent::__construct($processInstanceId, $ancestorActivityInstanceId);
        }
        $this->activityId = $activityId;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $processInstance = $commandContext->getExecutionManager()->findExecutionById($this->processInstanceId);
        $processDefinition = $processInstance->getProcessDefinition();

        $activity = $processDefinition->findActivity($this->activityId);

        // forbid instantiation of compensation boundary events
        if ($activity !== null && "compensationBoundaryCatch" == $activity->getProperty("type")) {
            throw new ProcessEngineException("Cannot start before activity " . $this->activityId . "; activity " .
            "is a compensation boundary event.");
        }

        return parent::execute($commandContext);
    }

    protected function getTargetFlowScope(ProcessDefinitionImpl $processDefinition): ScopeImpl
    {
        $activity = $processDefinition->findActivity($this->activityId);
        return $activity->getFlowScope();
    }

    public function getTargetElement(ProcessDefinitionImpl $processDefinition): CoreModelElement
    {
        $activity = $processDefinition->findActivity($this->activityId);
        return $activity;
    }

    public function getTargetElementId(): ?string
    {
        return $this->activityId;
    }

    protected function describe(): ?string
    {
        $sb = "";
        $sb .= "Start before activity '";
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
