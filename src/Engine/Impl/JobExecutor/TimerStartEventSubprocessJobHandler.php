<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;
use BpmPlatform\Engine\Impl\Pvm\Process\ActivityImpl;

class TimerStartEventSubprocessJobHandler extends TimerEventJobHandler
{
    public const TYPE = "timer-start-event-subprocess";

    public function getType(): string
    {
        return self::TYPE;
    }

    public function execute(TimerJobConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        $activityId = $configuration->getTimerElementKey();
        $eventSubprocessActivity = $execution->getProcessDefinition()
            ->findActivity($activityId);

        if ($eventSubprocessActivity != null) {
            $execution->executeEventHandlerActivity($eventSubprocessActivity);
        } else {
            throw new ProcessEngineException("Error while triggering event subprocess using timer start event: cannot find activity with id '" . $configuration . "'.");
        }
    }
}
