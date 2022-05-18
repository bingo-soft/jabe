<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Authorization\{
    Permissions,
    Resources
};
use Jabe\Engine\History\{
    HistoricActivityInstanceInterface,
    HistoricProcessInstanceInterface
};
use Jabe\Engine\Impl\{
    ProcessEngineLogger,
    ProcessInstantiationBuilderImpl,
    RestartProcessInstanceBuilderImpl
};
use Jabe\Engine\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\Concurrent\RunnableInterface;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Repository\ProcessDefinitionInterface;
use Jabe\Engine\Variable\VariableMapInterface;
use Jabe\Engine\Variable\Impl\VariableMapImpl;

class RestartProcessInstancesCmd extends AbstractRestartProcessInstanceCmd
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function __construct(CommandExecutor $commandExecutor, RestartProcessInstanceBuilderImpl $builder)
    {
        parent::__construct($commandExecutor, $builder);
    }

    public function execute(CommandContext $commandContext)
    {
        $instructions = $this->builder->getInstructions();

        EnsureUtil::ensureNotEmpty("Restart instructions cannot be empty", "instructions", $instructions);

        $processInstanceIds = $this->collectProcessInstanceIds();

        EnsureUtil::ensureNotEmpty("Process instance ids cannot be empty", "Process instance ids", $processInstanceIds);

        EnsureUtil::ensureNotContainsNull("Process instance ids cannot be null", "Process instance ids", $processInstanceIds);

        $processDefinition = $this->getProcessDefinition($commandContext, $this->builder->getProcessDefinitionId());
        EnsureUtil::ensureNotNull("Process definition cannot be found", "processDefinition", $processDefinition);

        $this->checkAuthorization($commandContext, $processDefinition);

        $this->writeUserOperationLog($commandContext, $processDefinition, count($processInstanceIds), false);

        $processDefinitionId = $this->builder->getProcessDefinitionId();

        $scope = $this;
        $runnable = new class ($scope, $commandContext, $processInstanceIds, $processDefinitionId) implements RunnableInterface {

            private $scope;
            private $commandContext;
            private $processInstanceIds;
            private $processDefinition;
            private $processDefinitionId;
            private $instructions;

            public function __construct($scope, $commandContext, $processInstanceIds, $processDefinition, $processDefinitionId, $instructions)
            {
                $this->commandContext = $commandContext;
                $this->processInstanceIds = $processInstanceIds;
                $this->processDefinition = $processDefinition;
                $this->processDefinitionId = $processDefinitionId;
                $this->instructions = $instructions;
            }

            public function run(): void
            {
                foreach ($this->processInstanceIds as $processInstanceId) {
                    $historicProcessInstance = $scope->getHistoricProcessInstance($this->commandContext, $processInstanceId);

                    EnsureUtil::ensureNotNull(
                        "Historic process instance cannot be found",
                        "historicProcessInstanceId",
                        $historicProcessInstance
                    );

                    $scope->ensureHistoricProcessInstanceNotActive($historicProcessInstance);
                    $scope->ensureSameProcessDefinition($historicProcessInstance, $this->processDefinitionId);

                    $instantiationBuilder = $scope->getProcessInstantiationBuilder($commandExecutor, $this->processDefinitionId);
                    $scope->applyProperties($instantiationBuilder, $this->processDefinition, $historicProcessInstance);

                    $modificationBuilder = $instantiationBuilder->getModificationBuilder();

                    $instantiationBuilder->setModificationOperations($this->instructions);

                    $variables = $this->collectVariables($commandContext, $historicProcessInstance);
                    $instantiationBuilder->setVariables($variables);

                    $instantiationBuilder->execute($this->builder->isSkipCustomListeners(), $this->builder->isSkipIoMappings());
                }
            }
        };

        ProcessApplicationContextUtil::doContextSwitch($runnable, $processDefinition);

        return null;
    }

    protected function checkAuthorization(CommandContext $commandContext, ProcessDefinitionInterface $processDefinition): void
    {
        $commandContext->getAuthorizationManager()
            ->checkAuthorization(Permissions::readHistory(), Resources::processDefinition(), $processDefinition->getKey());
    }

    public function getHistoricProcessInstance(CommandContext $commandContext, string $processInstanceId): HistoricProcessInstanceInterface
    {
        $historyService = $commandContext->getProcessEngineConfiguration()->getHistoryService();
        return $historyService->createHistoricProcessInstanceQuery()
            ->processInstanceId($processInstanceId)
            ->singleResult();
    }

    public function ensureSameProcessDefinition(HistoricProcessInstanceInterface $instance, string $processDefinitionId): void
    {
        if ($processDefinitionId != $instance->getProcessDefinitionId()) {
            //throw LOG.processDefinitionOfHistoricInstanceDoesNotMatchTheGivenOne(
            //    instance, processDefinitionId);
            throw new \Exception("processDefinitionOfHistoricInstanceDoesNotMatchTheGivenOne $processDefinitionId");
        }
    }

    public function ensureHistoricProcessInstanceNotActive(HistoricProcessInstanceInterface $instance): void
    {
        if ($instance->getEndTime() == null) {
            //throw LOG.historicProcessInstanceActive(instance);
        }
    }

    public function getProcessInstantiationBuilder(CommandExecutor $commandExecutor, string $processDefinitionId): ProcessInstantiationBuilderImpl
    {
        return ProcessInstantiationBuilderImpl::createProcessInstanceById($commandExecutor, $processDefinitionId);
    }

    public function applyProperties(
        ProcessInstantiationBuilderImpl $instantiationBuilder,
        ProcessDefinitionInterface $processDefinition,
        HistoricProcessInstanceInterface $processInstance
    ): void {
        $tenantId = $processInstance->getTenantId();
        if ($processDefinition->getTenantId() == null && $tenantId != null) {
            $instantiationBuilder->tenantId($tenantId);
        }

        if (!$this->builder->isWithoutBusinessKey()) {
            $instantiationBuilder->businessKey($processInstance->getBusinessKey());
        }
    }

    public function collectVariables(CommandContext $commandContext, HistoricProcessInstanceInterface $processInstance): ?VariableMapInterface
    {
        $variables = null;

        if ($this->builder->isInitialVariables()) {
            $variables = $this->collectInitialVariables($commandContext, $processInstance);
        } else {
            $variables = $this->collectLastVariables($commandContext, $processInstance);
        }

        return $variables;
    }

    protected function collectInitialVariables(CommandContext $commandContext, HistoricProcessInstanceInterface $processInstance): ?VariableMapInterface
    {
        $historyService = $commandContext->getProcessEngineConfiguration()
            ->getHistoryService();

        $historicDetails = $historyService->createHistoricDetailQuery()
            ->variableUpdates()
            ->executionId($processInstance->getId())
            ->initial()
            ->list();

        // legacy behavior < 7.13: the initial flag is never set for instances started
        // in these versions. We must perform the old logic of finding initial variables
        if (count($historicDetails) == 0) {
            $startActivityInstance = $this->resolveStartActivityInstance($processInstance);

            if ($startActivityInstance != null) {
                $queryWithStartActivities = $historyService->createHistoricDetailQuery()
                        ->variableUpdates()
                        ->activityInstanceId($startActivityInstance->getId())
                        ->executionId($processInstance->getId());
                $historicDetails = $queryWithStartActivities
                        ->sequenceCounter(1)
                        ->list();
            }
        }

        $variables = new VariableMapImpl();
        foreach ($historicDetails as $detail) {
            $variableUpdate = $detail;
            $variables->putValueTyped($variableUpdate->getVariableName(), $variableUpdate->getTypedValue());
        }

        return $variables;
    }

    protected function collectLastVariables(CommandContext $commandContext, HistoricProcessInstanceInterface $processInstance): ?VariableMapInterface
    {
        $historyService = $commandContext->getProcessEngineConfiguration()
            ->getHistoryService();

        $historicVariables = $historyService->createHistoricVariableInstanceQuery()
                ->executionIdIn($processInstance->getId())
                ->list();

        $variables = new VariableMapImpl();
        foreach ($historicVariables as $variable) {
            $variables->putValueTyped($variable->getName(), $variable->getTypedValue());
        }

        return $variables;
    }

    protected function resolveStartActivityInstance(HistoricProcessInstance $processInstance): HistoricActivityInstanceInterface
    {
        $historyService = Context::getProcessEngineConfiguration()->getHistoryService();

        $processInstanceId = $processInstance->getId();
        $startActivityId = $processInstance->getStartActivityId();

        EnsureUtil::ensureNotNull("startActivityId", "startActivityId", $this->startActivityId);

        $historicActivityInstances = $historyService
            ->createHistoricActivityInstanceQuery()
            ->processInstanceId($processInstanceId)
            ->activityId($startActivityId)
            ->orderPartiallyByOccurrence()
            ->asc()
            ->list();

        EnsureUtil::ensureNotEmpty("historicActivityInstances", "historicActivityInstances", $historicActivityInstances);

        $startActivityInstance = $historicActivityInstances[0];
        return $startActivityInstance;
    }
}
