<?php

namespace Jabe\Engine\Migration;

interface MigrationPlanValidationReportInterface
{
    /**
     * @return the migration plan of the validation report
     */
    public function getMigrationPlan(): MigrationPlanInterface;

    /**
     * @return bool - true if instructions reports exist, false otherwise
     */
    public function hasInstructionReports(): bool;

    /**
     * @return all instruction reports
     */
    public function getInstructionReports(): array;
}
