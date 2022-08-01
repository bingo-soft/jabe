<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Repository\ProcessDefinitionInterface;

class TimerStartEventJobHandler extends TimerEventJobHandler
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    public const TYPE = "timer-start-event";

    public function getType(): string
    {
        return self::TYPE;
    }

    public function execute(TimerJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $deploymentCache = Context::getProcessEngineConfiguration()
                ->getDeploymentCache();

        $definitionKey = $configuration->getTimerElementKey();
        $processDefinition = $deploymentCache->findDeployedLatestProcessDefinitionByKeyAndTenantId($definitionKey, $tenantId);

        try {
            $this->startProcessInstance($commandContext, $tenantId, $processDefinition);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function startProcessInstance(CommandContext $commandContext, ?string $tenantId, ProcessDefinitionInterface $processDefinition): void
    {
        if (!$processDefinition->isSuspended()) {
            $runtimeService = $commandContext->getProcessEngineConfiguration()->getRuntimeService();
            $runtimeService->createProcessInstanceByKey($processDefinition->getKey())->processDefinitionTenantId($tenantId)->execute();
        } else {
            //LOG.ignoringSuspendedJob(processDefinition);
        }
    }
}
