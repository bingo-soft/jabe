<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl,
    ScopeImpl
};

class ActivityBeforeInstantiationCmd extends AbstractInstantiationCmd
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

    public function execute(CommandContext $commandContext)
    {
        $processInstance = $commandContext->getExecutionManager()->findExecutionById($processInstanceId);
        $processDefinition = $processInstance->getProcessDefinition();

        $activity = $processDefinition->findActivity($activityId);

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

    protected function getTargetElement(ProcessDefinitionImpl $processDefinition): CoreModelElement
    {
        $activity = $processDefinition->findActivity($activityId);
        return $activity;
    }

    public function getTargetElementId(): string
    {
        return $this->activityId;
    }

    protected function describe(): string
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
