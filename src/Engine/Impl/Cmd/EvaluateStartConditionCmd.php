<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\ConditionEvaluationBuilderImpl;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ProcessDefinitionEntity
};
use Jabe\Engine\Impl\Runtime\{
    ConditionHandlerInterface,
    ConditionHandlerResult,
    CorrelationSet
};
use Jabe\Engine\Runtime\ProcessInstanceInterface;

class EvaluateStartConditionCmd implements CommandInterface
{
    protected $builder;

    public function __construct(ConditionEvaluationBuilderImpl $builder)
    {
        $this->builder = $builder;
    }

    public function execute(CommandContext $commandContext)
    {
        $conditionHandler = $commandContext->getProcessEngineConfiguration()->getConditionHandler();
        $conditionSet = new ConditionSet($builder);

        $results = $conditionHandler->evaluateStartCondition($commandContext, $conditionSet);

        foreach ($results as $conditionHandlerResult) {
            $this->checkAuthorization($commandContext, $conditionHandlerResult);
        }

        $processInstances = [];
        foreach ($results as $conditionHandlerResult) {
            $processInstances[] = $this->instantiateProcess($commandContext, $conditionHandlerResult);
        }

        return $processInstances;
    }

    protected function checkAuthorization(CommandContext $commandContext, ConditionHandlerResult $result): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $definition = $result->getProcessDefinition();
            $checker->checkCreateProcessInstance($definition);
        }
    }

    protected function instantiateProcess(CommandContext $commandContext, ConditionHandlerResult $result): ProcessInstanceInterface
    {
        $processDefinitionEntity = $result->getProcessDefinition();

        $startEvent = $processDefinitionEntity->findActivity($result->getActivity()->getActivityId());
        $processInstance = $processDefinitionEntity->createProcessInstance($this->builder->getBusinessKey(), $startEvent);
        $processInstance->start($this->builder->getVariables());

        return $processInstance;
    }
}
