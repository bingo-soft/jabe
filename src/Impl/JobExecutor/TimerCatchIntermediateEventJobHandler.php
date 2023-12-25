<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\JobHandlerConfigurationInterface;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Util\EnsureUtil;

class TimerCatchIntermediateEventJobHandler extends TimerEventJobHandler
{
    public const TYPE = "timer-intermediate-transition";

    public function getType(): ?string
    {
        return self::TYPE;
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId, ...$args): void
    {
        $activityId = $configuration->getTimerElementKey();
        $intermediateEventActivity = $execution->getProcessDefinition()->findActivity($activityId);

        EnsureUtil::ensureNotNull("Error while firing timer: intermediate event activity " . $configuration . " not found", "intermediateEventActivity", $intermediateEventActivity);

        try {
            if ($activityId == $execution->getActivityId()) {
                // Regular Intermediate timer catch
                $execution->signal("signal", null);
            } else {
                // Event based gateway
                $execution->executeEventHandlerActivity($intermediateEventActivity);
            }
        } catch (\Throwable $e) {
            throw new ProcessEngineException("exception during timer execution 2: " . $e->getMessage(), $e);
        }
    }
}
