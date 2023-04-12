<?php

namespace Jabe\Impl\Core\Model;

use Jabe\ProcessEngineException;
use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ProcessDefinitionEntity
};

class DefaultCallableElementTenantIdProvider implements ParameterValueProviderInterface
{
    public function getValue(?VariableScopeInterface $execution)
    {
        if ($execution instanceof ExecutionEntity) {
            return $this->getProcessDefinitionTenantId($execution);
        } else {
            throw new ProcessEngineException("Unexpected execution of type " . get_class($execution));
        }
        /*elseif (execution instanceof CaseExecutionEntity) {
            return getCaseDefinitionTenantId((CaseExecutionEntity) execution);
        }*/
    }

    protected function getProcessDefinitionTenantId(ExecutionEntity $execution): ?string
    {
        $processDefinition = $execution->getProcessDefinition();
        return $processDefinition->getTenantId();
    }

    public function isDynamic(): bool
    {
        return false;
    }

    /*protected String getCaseDefinitionTenantId(CaseExecutionEntity caseExecution) {
      CaseDefinitionEntity caseDefinition = (CaseDefinitionEntity) caseExecution.getCaseDefinition();
      return caseDefinition.getTenantId();
    }*/
}
