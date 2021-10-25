<?php

namespace BpmPlatform\Engine\Migration;

interface MigratingTransitionInstanceValidationReportInterface
{
    /**
     * @return the id of the source scope of the migrating transition instance
     */
    public function getSourceScopeId(): string;

    /**
     * @return the transition instance id of this report
     */
    public function getTransitionInstanceId(): string;

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
