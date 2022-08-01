<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Migration\Instance\{
    MigratingActivityInstance,
    MigratingCalledProcessInstance
};
use Jabe\Engine\Impl\Migration\Instance\Parser\MigratingInstanceParseContext;
use Jabe\Engine\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    MigrationObserverBehaviorInterface
};
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl
};
use Jabe\Engine\Variable\VariableMapInterface;

class CallActivityBehavior extends CallableElementActivityBehavior implements MigrationObserverBehaviorInterface
{
    public function __construct($param = null)
    {
        parent::__construct($param);
    }

    protected function startInstance(ActivityExecutionInterface $execution, VariableMapInterface $variables, ?string $businessKey = null): void
    {
        $executionEntity = $execution;

        $definition = $this->getProcessDefinitionToCall(
            $executionEntity,
            $executionEntity->getProcessDefinitionTenantId(),
            $this->getCallableElement()
        );
        $processInstance = $execution->createSubProcessInstance($definition, $businessKey);
        $processInstance->start($variables);
    }

    public function migrateScope(ActivityExecutionInterface $scopeExecution): void
    {
    }

    public function onParseMigratingInstance(MigratingInstanceParseContext $parseContext, MigratingActivityInstance $migratingInstance): void
    {
        $callActivity = $migratingInstance->getSourceScope();

        // A call activity is typically scope and since we guarantee stability of scope executions during migration,
        // the superExecution link does not have to be maintained during migration.
        // There are some exceptions, though: A multi-instance call activity is not scope and therefore
        // does not have a dedicated scope execution. In this case, the link to the super execution
        // must be maintained throughout migration
        if (!$callActivity->isScope()) {
            $callActivityExecution = $migratingInstance->resolveRepresentativeExecution();
            $calledProcessInstance = $callActivityExecution->getSubProcessInstance();
            $migratingInstance->addMigratingDependentInstance(new MigratingCalledProcessInstance($calledProcessInstance));
        }
    }
}
