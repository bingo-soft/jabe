<?php

namespace Jabe\Engine\Impl\Core\Model;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ProcessDefinitionEntity
};

class DefaultCallableElementTenantIdProvider implements ParameterValueProviderInterface
{
    public function getValue(VariableScopeInterface $execution)
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
        $rocessDefinition = $execution->getProcessDefinition();
        return $processDefinition->getTenantId();
    }
    /*protected String getCaseDefinitionTenantId(CaseExecutionEntity caseExecution) {
      CaseDefinitionEntity caseDefinition = (CaseDefinitionEntity) caseExecution.getCaseDefinition();
      return caseDefinition.getTenantId();
    }*/
}
