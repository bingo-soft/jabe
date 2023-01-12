<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\JobHandlerConfigurationInterface;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Util\EnsureUtil;

class TimerExecuteNestedActivityJobHandler extends TimerEventJobHandler
{
    public const TYPE = "timer-transition";

    public function getType(): ?string
    {
        return self::TYPE;
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $activityId = $configuration->getTimerElementKey();
        $activity = $execution->getProcessDefinition()->findActivity($activityId);

        EnsureUtil::ensureNotNull("Error while firing timer: boundary event activity " . $configuration . " not found", "boundary event activity", $activity);

        try {
            $execution->executeEventHandlerActivity($activity);
        } catch (\Exception $e) {
            throw new ProcessEngineException("exception during timer execution: " . $e->getMessage(), $e);
        }
    }
}
