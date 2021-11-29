<?php

namespace BpmPlatform\Engine\Impl\Pvm\Delegate;

use BpmPlatform\Engine\Impl\Migration\Instance\MigratingActivityInstance;
use BpmPlatform\Engine\Impl\Migration\Instance\Parser\MigratingInstanceParseContext;

interface MigrationObserverBehaviorInterface
{
    /**
     * Implement to perform activity-specific migration behavior that is not
     * covered by the regular migration procedure. Called after the scope execution and any ancestor executions
     * have been migrated to their target activities and process definition.
     */
    public function migrateScope(ActivityExecutionInterface $scopeExecution): void;

    /**
     * Callback to implement behavior specific parsing (e.g. adding additional dependent entities).
     */
    public function onParseMigratingInstance(MigratingInstanceParseContext $parseContext, MigratingActivityInstance $migratingInstance): void;
}
