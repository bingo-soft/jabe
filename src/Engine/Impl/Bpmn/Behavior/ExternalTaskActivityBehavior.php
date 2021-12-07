<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\PriorityProviderInterface;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use BpmPlatform\Engine\Impl\Migration\Instance\{
    MigratingActivityInstance,
    MigratingExternalTaskInstance
};
use BpmPlatform\Engine\Impl\Migration\Instance\Parser\MigratingInstanceParseContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExternalTaskEntity
};
use BpmPlatform\Engine\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    MigrationObserverBehaviorInterface
};

class ExternalTaskActivityBehavior extends AbstractBpmnActivityBehavior implements MigrationObserverBehaviorInterface
{
    protected $topicNameValueProvider;
    protected $priorityValueProvider;

    public function __construct(ParameterValueProviderInterface $topicName, ParameterValueProviderInterface $paramValueProvider)
    {
        $this->topicNameValueProvider = $topicName;
        $this->priorityValueProvider = $paramValueProvider;
    }

    public function execute(ActivityExecutionInterface $execution): void
    {
        $executionEntity = $execution;
        $provider = Context::getProcessEngineConfiguration()->getExternalTaskPriorityProvider();

        $priority = $provider->determinePriority($executionEntity, $this, null);
        $topic = $this->topicNameValueProvider->getValue($executionEntity);

        ExternalTaskEntity::createAndInsert($executionEntity, $topic, $priority);
    }

    public function signal(ActivityExecutionInterface $execution, string $signalName, $signalData): void
    {
        $this->leave($execution);
    }

    public function getPriorityValueProvider(): ParameterValueProviderInterface
    {
        return $this->priorityValueProvider;
    }

    public function migrateScope(ActivityExecutionInterface $scopeExecution): void
    {
    }

    public function onParseMigratingInstance(MigratingInstanceParseContext $parseContext, MigratingActivityInstance $migratingInstance): void
    {
        $execution = $migratingInstance->resolveRepresentativeExecution();

        foreach ($execution->getExternalTasks() as $task) {
            $migratingTask = new MigratingExternalTaskInstance($task, $migratingInstance);
            $migratingInstance->addMigratingDependentInstance($migratingTask);
            $parseContext->consume($task);
            $parseContext->submit($migratingTask);
        }
    }
}
