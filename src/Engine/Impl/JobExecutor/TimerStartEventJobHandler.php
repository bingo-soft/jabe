<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Deploy\Cache\DeploymentCache;
use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;
use BpmPlatform\Engine\Repository\ProcessDefinitionInterface;

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
