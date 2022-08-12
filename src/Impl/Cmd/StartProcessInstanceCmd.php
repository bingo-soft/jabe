<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionVariableSnapshotObserver,
    ProcessInstanceWithVariablesImpl,
    PropertyChange
};

class StartProcessInstanceCmd implements CommandInterface
{
    protected $instantiationBuilder;

    public function __construct(ProcessInstantiationBuilderImpl $instantiationBuilder)
    {
        $this->instantiationBuilder = $instantiationBuilder;
    }

    public function execute(CommandContext $commandContext)
    {
        $processDefinition = (new GetDeployedProcessDefinitionCmd($this->instantiationBuilder, false))->execute($commandContext);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateProcessInstance($processDefinition);
        }

        // Start the process instance
        $processInstance = $processDefinition->createProcessInstance(
            $this->instantiationBuilder->getBusinessKey(),
            //$this->instantiationBuilder->getCaseInstanceId()
            null
        );

        if ($this->instantiationBuilder->getTenantId() !== null) {
            $processInstance->setTenantId($this->instantiationBuilder->getTenantId());
        }

        $variablesListener = new ExecutionVariableSnapshotObserver($processInstance);

        $processInstance->start($this->instantiationBuilder->getVariables());

        $commandContext->getOperationLogManager()->logProcessInstanceOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_CREATE,
            $processInstance->getId(),
            $processInstance->getProcessDefinitionId(),
            $processInstance->getProcessDefinition()->getKey(),
            [PropertyChange::emptyChange()]
        );

        return new ProcessInstanceWithVariablesImpl($processInstance, $variablesListener->getVariables());
    }
}
