<?php

namespace Jabe\Engine\Impl\Runtime;

use Jabe\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use Jabe\Engine\Impl\Pvm\Process\ActivityImpl;

class ConditionHandlerResult
{
    private $processDefinition;
    private $activity;

    public function __construct(ProcessDefinitionEntity $processDefinition, ActivityImpl $activity)
    {
        $this->setProcessDefinition($processDefinition);
        $this->setActivity($activity);
    }

    public function getProcessDefinition(): ProcessDefinitionEntity
    {
        return $this->processDefinition;
    }

    public function setProcessDefinition(ProcessDefinitionEntity $processDefinition): void
    {
        $this->processDefinition = $processDefinition;
    }

    public function getActivity(): ActivityImpl
    {
        return $this->activity;
    }

    public function setActivity(?ActivityImpl $activity = null): void
    {
        $this->activity = $activity;
    }

    public static function matchedProcessDefinition(ProcessDefinitionEntity $processDefinition, ActivityImpl $startActivityId): ConditionHandlerResult
    {
        $conditionHandlerResult = new ConditionHandlerResult($processDefinition, $startActivityId);
        return $conditionHandlerResult;
    }
}
