<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity,
    JobEntity
};

class ProcessEventJobHandler implements JobHandlerInterface
{
    public const TYPE = "event";

    public function getType(): string
    {
        return self::TYPE;
    }

    public function execute(JobHandlerConfigurationInterface $configuration, ExecutionEntity $execution, CommandContext $commandContext, string $tenantId = null): void
    {
        // lookup subscription:
        $eventSubscriptionId = $configuration->getEventSubscriptionId();
        $eventSubscription = $commandContext->getEventSubscriptionManager()
            ->findEventSubscriptionById($eventSubscriptionId);

        // if event subscription is null, ignore
        if ($eventSubscription !== null) {
            $eventSubscription->eventReceived(null, false);
        }
    }

    public function newConfiguration(string $canonicalString): JobHandlerConfigurationInterface
    {
        return new EventSubscriptionJobConfiguration($canonicalString);
    }

    public function onDelete(JobHandlerConfigurationInterface $configuration, JobEntity $jobEntity): void
    {
        // do nothing
    }
}
