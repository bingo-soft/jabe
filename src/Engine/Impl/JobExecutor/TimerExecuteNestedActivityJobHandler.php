<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;
use BpmPlatform\Engine\Impl\Pvm\Process\ActivityImpl;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class TimerExecuteNestedActivityJobHandler extends TimerEventJobHandler
{
    public const TYPE = "timer-transition";

    public function getType(): string
    {
        return self::TYPE;
    }

    public function execute(TimerJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
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
