<?php

namespace BpmPlatform\Engine\Migration;

interface MigrationInstructionValidationReportInterface
{
    /**
     * @return the migration instruction of this report
     */
    public function getMigrationInstruction(): MigrationInstructionInterface;

    /**
     * @return bool - true if the report contains failures, false otherwise
     */
    public function hasFailures(): bool;

    /**
     * @return the list of failure messages
     */
    public function getFailures(): array;
}
