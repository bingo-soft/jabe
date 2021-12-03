<?php

namespace BpmPlatform\Engine\Migration;

interface MigratingProcessInstanceValidationReportInterface
{
    /**
     * @return the id of the process instance that the migration plan is applied to
     */
    public function getProcessInstanceId(): string;

    /**
     * @return the list of general failures of the migrating process instance
     */
    public function getFailures(): array;

    /**
     * @return bool - true if general failures or activity instance validation reports exist, false otherwise
     */
    public function hasFailures(): bool;

    /**
     * @return the list of activity instance validation reports
     */
    public function getActivityInstanceReports(): array;

    /**
     * @return the list of transition instance validation reports
     */
    public function getTransitionInstanceReports(): array;
}
