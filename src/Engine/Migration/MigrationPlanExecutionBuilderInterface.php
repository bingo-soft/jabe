<?php

namespace BpmPlatform\Engine\Migration;

use BpmPlatform\Engine\{
    BatchPermissions,
    AuthorizationException,
    BadUserRequestException
};
use BpmPlatform\Engine\Authorization\{
    Permissions,
    Resources
};
use BpmPlatform\Engine\Batch\BatchInterface;
use BpmPlatform\Engine\Runtime\ProcessInstanceQueryInterface;

interface MigrationPlanExecutionBuilderInterface
{
    /**
     * @param processInstanceIds the process instance ids to migrate.
     */
    public function processInstanceIds(array $processInstanceIds): MigrationPlanExecutionBuilderInterface;

    /**
     * @param processInstanceQuery a query which selects the process instances to migrate.
     *   Query results are restricted to process instances for which the user has {@link Permissions#READ} permission.
     */
    public function processInstanceQuery(ProcessInstanceQueryInterface $processInstanceQuery): MigrationPlanExecutionBuilderInterface;

    /**
     * Skips custom execution listeners when creating/removing activity instances during migration
     */
    public function skipCustomListeners(): MigrationPlanExecutionBuilderInterface;

    /**
     * Skips io mappings when creating/removing activity instances during migration
     */
    public function skipIoMappings(): MigrationPlanExecutionBuilderInterface;

    /**
     * Execute the migration synchronously.
     *
     * @throws MigratingProcessInstanceValidationException if the migration plan contains
     *  instructions that are not applicable to any of the process instances
     * @throws AuthorizationException
     *   if the user has not all of the following permissions
     *   <ul>
     *      <li>if the user has no {@link Permissions#UPDATE} permission on {@link Resources#PROCESS_INSTANCE} or</li>
     *      <li>no {@link Permissions#UPDATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     * @throws BadUserRequestException
     *   When the affected instances count exceeds the maximum results limit. A maximum results
     *   limit can be specified with the process engine configuration property
     *   <code>queryMaxResultsLimit</code> (default {@link Integer#MAX_VALUE}).
     *   Please use the batch operation {@link #executeAsync()} instead.
     */
    public function execute(): void;

    /**
     * Execute the migration asynchronously as batch. The returned batch
     * can be used to track the progress of the migration.
     *
     * @return the batch which executes the migration asynchronously.
     *
     * @throws AuthorizationException
     *   if the user has not all of the following permissions
     *   <ul>
     *     <li>{@link Permissions#MIGRATE_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION} for source and target</li>
     *     <li>{@link Permissions#CREATE} or {@link BatchPermissions#CREATE_BATCH_MIGRATE_PROCESS_INSTANCES} permission on {@link Resources#BATCH}</li>
     *   </ul>
     */
    public function executeAsync(): BatchInterface;
}
