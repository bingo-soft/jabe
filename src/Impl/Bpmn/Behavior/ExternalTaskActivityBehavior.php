<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Impl\Migration\Instance\{
    MigratingActivityInstance,
    MigratingExternalTaskInstance
};
use Jabe\Impl\Migration\Instance\Parser\MigratingInstanceParseContext;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExternalTaskEntity
};
use Jabe\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    MigrationObserverBehaviorInterface
};

class ExternalTaskActivityBehavior extends AbstractBpmnActivityBehavior implements MigrationObserverBehaviorInterface
{
    protected $topicNameValueProvider;
    protected $priorityValueProvider;

    public function __construct(ParameterValueProviderInterface $topicName, ParameterValueProviderInterface $paramValueProvider)
    {
        parent::__construct();
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

    public function signal(ActivityExecutionInterface $execution, ?string $signalName, $signalData): void
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
