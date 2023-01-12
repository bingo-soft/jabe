<?php

namespace Jabe\Impl\Context;

use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ProcessDefinitionEntity
};

class ExecutionContext extends CoreExecutionContext
{
    public function __construct(ExecutionEntity $execution)
    {
        parent::__construct($execution);
    }

    public function getProcessInstance(): ExecutionEntity
    {
        return $this->execution->getProcessInstance();
    }

    public function getProcessDefinition(): ProcessDefinitionEntity
    {
        return $this->execution->getProcessDefinition();
    }

    protected function getDeploymentId(): ?string
    {
        return $this->getProcessDefinition()->getDeploymentId();
    }
}
