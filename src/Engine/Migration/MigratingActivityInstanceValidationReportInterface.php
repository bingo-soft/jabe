<?php

namespace BpmPlatform\Engine\Migration;

interface MigratingActivityInstanceValidationReportInterface
{
    /**
     * @return the id of the source scope of the migrated activity instance
     */
    public function getSourceScopeId(): string;

    /**
     * @return the activity instance id of this report
     */
    public function getActivityInstanceId(): string;

    /**
     * @return the migration instruction that cannot be applied
     */
    public function getMigrationInstruction(): MigrationInstructionInterface;

    /**
     * @return true if the reports contains failures, false otherwise
     */
    public function hasFailures(): bool;

    /**
     * @return the list of failures
     */
    public function getFailures(): array;
}
