<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Pvm\Process\{
    ProcessDefinitionImpl,
    ScopeImpl,
    TransitionImpl
};

class TransitionInstantiationCmd extends AbstractInstantiationCmd
{
    protected $transitionId;

    public function __construct(?string $processInstanceId, string $transitionId, ?string $ancestorActivityInstanceId)
    {
        parent::__construct($processInstanceId, $ancestorActivityInstanceId);
        $this->transitionId = $transitionId;
    }

    protected function getTargetFlowScope(ProcessDefinitionImpl $processDefinition): ScopeImpl
    {
        $transition = $processDefinition->findTransition($this->transitionId);
        return $transition->getSource()->getFlowScope();
    }

    protected function getTargetElement(ProcessDefinitionImpl $processDefinition): CoreModelElement
    {
        $transition = $processDefinition->findTransition($this->transitionId);
        return $transition;
    }

    public function getTargetElementId(): string
    {
        return $this->transitionId;
    }

    protected function describe(): string
    {
        $sb = "";
        $sb .= "Start transition '";
        $sb .= $this->transitionId;
        $sb .= "'";
        if ($this->ancestorActivityInstanceId != null) {
            $sb .= " with ancestor activity instance '";
            $sb .= $this->ancestorActivityInstanceId;
            $sb .= "'";
        }

        return $sb;
    }
}
