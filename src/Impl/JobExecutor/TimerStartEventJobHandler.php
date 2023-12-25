<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\JobHandlerConfigurationInterface;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Repository\ProcessDefinitionInterface;

class TimerStartEventJobHandler extends TimerEventJobHandler
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    public const TYPE = "timer-start-event";

    public function getType(): ?string
    {
        return self::TYPE;
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ?ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId, ...$args): void
    {
        $deploymentCache = Context::getProcessEngineConfiguration()
                ->getDeploymentCache();

        $definitionKey = $configuration->getTimerElementKey();
        $processDefinition = $deploymentCache->findDeployedLatestProcessDefinitionByKeyAndTenantId($definitionKey, $tenantId);

        try {
            $this->startProcessInstance($commandContext, $tenantId, $processDefinition, ...$args);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    protected function startProcessInstance(CommandContext $commandContext, ?string $tenantId, ProcessDefinitionInterface $processDefinition, ...$args): void
    {
        if (!$processDefinition->isSuspended()) {
            $runtimeService = $commandContext->getProcessEngineConfiguration()->getRuntimeService();
            $runtimeService->createProcessInstanceByKey($processDefinition->getKey())->processDefinitionTenantId($tenantId)->execute(false, false, ...$args);
        } else {
            //LOG.ignoringSuspendedJob(processDefinition);
        }
    }
}
