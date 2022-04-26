<?php

namespace Jabe\Engine\Migration;

use Jabe\Engine\AuthorizationException;
use Jabe\Engine\Authorization\{
    Permissions,
    Resources
};

interface MigrationPlanBuilderInterface
{
    /**
     * Automatically adds a set of instructions for activities that are <em>equivalent</em> in both
     * process definitions. By default, this is given if two activities are both user tasks, are on the same
     * level of sub process, and have the same id.
     */
    public function mapEqualActivities(): MigrationInstructionsBuilderInterface;

    /**
     * Adds a migration instruction that maps activity instances of the source activity (of the source process definition)
     * to activity instances of the target activity (of the target process definition)
     */
    public function mapActivities(string $sourceActivityId, string $targetActivityId): MigrationInstructionBuilderInterface;

    /**
     * @return a migration plan with all previously specified instructions
     *
     * @throws MigrationPlanValidationException if the migration plan contains instructions that are not valid
     * @throws AuthorizationException
     *         if the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_DEFINITION}
     *         for both, source and target process definition.
     */
    public function build(): MigrationPlanInterface;
}
