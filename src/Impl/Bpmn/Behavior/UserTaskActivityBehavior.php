<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\El\ExpressionManagerInterface;
use Jabe\Impl\Migration\Instance\{
    MigratingActivityInstance,
    MigratingUserTaskInstance
};
use Jabe\Impl\Migration\Instance\Parser\MigratingInstanceParseContext;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    TaskEntity,
    TaskState,
    VariableInstanceEntity
};
use Jabe\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    MigrationObserverBehaviorInterface
};
use Jabe\Impl\Task\{
    TaskDecorator,
    TaskDefinition
};

class UserTaskActivityBehavior extends TaskActivityBehavior implements MigrationObserverBehaviorInterface
{
    protected $taskDecorator;

    public function __construct(/*ExpressionManagerInterface|TaskDecorator*/$expressionManagerOrDecorator, ?TaskDefinition $taskDefinition = null, ?TaskDecorator $taskDecorator = null)
    {
        parent::__construct();
        if ($taskDecorator !== null) {
            $this->taskDecorator = $taskDecorator;
        } elseif ($expressionManagerOrDecorator !== null && $taskDefinition !== null) {
            $this->taskDecorator = new TaskDecorator($taskDefinition, $expressionManagerOrDecorator);
        } elseif ($expressionManagerOrDecorator instanceof TaskDecorator) {
            $this->taskDecorator = $expressionManagerOrDecorator;
        }
    }

    public function performExecution(ActivityExecutionInterface $execution): void
    {
        $task = new TaskEntity($execution);
        $task->insert();

        // initialize task properties
        $this->taskDecorator->decorate($task, $execution);

        // fire lifecycle events after task is initialized
        $task->transitionTo(TaskState::STATE_CREATED);
    }

    public function signal(ActivityExecutionInterface $execution, ?string $signalName, $ignalData): void
    {
        $this->leave($execution);
    }

    public function migrateScope(ActivityExecutionInterface $scopeExecution): void
    {
    }

    public function onParseMigratingInstance(MigratingInstanceParseContext $parseContext, MigratingActivityInstance $migratingInstance): void
    {
        $execution = $migratingInstance->resolveRepresentativeExecution();

        foreach ($execution->getTasks() as $task) {
            $migratingInstance->addMigratingDependentInstance(new MigratingUserTaskInstance($task, $migratingInstance));
            $parseContext->consume($task);

            $variables = $task->getVariablesInternal();

            if (!empty($variables)) {
                foreach ($variables as $variable) {
                    // we don't need to represent task variables in the migrating instance structure because
                    // they are migrated by the MigratingTaskInstance as well
                    $parseContext->consume($variable);
                }
            }
        }
    }

    public function getTaskDefinition(): TaskDefinition
    {
        return $this->taskDecorator->getTaskDefinition();
    }

    public function getExpressionManager(): ExpressionManagerInterface
    {
        return $this->taskDecorator->getExpressionManager();
    }

    public function getTaskDecorator(): TaskDecorator
    {
        return $this->taskDecorator;
    }
}
