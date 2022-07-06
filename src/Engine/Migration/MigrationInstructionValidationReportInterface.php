<?php

namespace Jabe\Engine\Migration;

interface MigrationInstructionValidationReportInterface
{
    /**
     * @return MigrationInstructionInterface the migration instruction of this report
     */
    public function getMigrationInstruction(): MigrationInstructionInterface;

    /**
     * @return bool - true if the report contains failures, false otherwise
     */
    public function hasFailures(): bool;

    /**
     * @return array the list of failure messages
     */
    public function getFailures(): array;
}
