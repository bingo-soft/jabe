<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;

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

        if ($eventSubprocessActivity !== null) {
            $execution->executeEventHandlerActivity($eventSubprocessActivity);
        } else {
            throw new ProcessEngineException("Error while triggering event subprocess using timer start event: cannot find activity with id '" . $configuration . "'.");
        }
    }
}
