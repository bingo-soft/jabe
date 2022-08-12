<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Util\EnsureUtil;

class TimerCatchIntermediateEventJobHandler extends TimerEventJobHandler
{
    public const TYPE = "timer-intermediate-transition";

    public function getType(): string
    {
        return self::TYPE;
    }

    public function execute(TimerJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
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
        } catch (\Exception $e) {
            throw new ProcessEngineException("exception during timer execution: " . $e->getMessage(), $e);
        }
    }
}
