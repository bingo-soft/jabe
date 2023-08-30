<?php

namespace Jabe\Migration;

use Jabe\{
    BatchPermissions,
    AuthorizationException,
    BadUserRequestException
};
use Jabe\Authorization\{
    Permissions,
    Resources
};
use Jabe\Batch\BatchInterface;
use Jabe\Runtime\ProcessInstanceQueryInterface;

interface MigrationPlanExecutionBuilderInterface
{
    /**
     * @param processInstanceIds the process instance ids to migrate.
     */
    public function processInstanceIds(array $processInstanceIds): MigrationPlanExecutionBuilderInterface;

    /**
     * @param processInstanceQuery a query which selects the process instances to migrate.
     *   Query results are restricted to process instances for which the user has Permissions#READ permission.
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
     *      <li>if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE or</li>
     *      <li>no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *   </ul>
     * @throws BadUserRequestException
     *   When the affected instances count exceeds the maximum results limit. A maximum results
     *   limit can be specified with the process engine configuration property
     *   <code>queryMaxResultsLimit</code> (default Integer#MAX_VALUE).
     *   Please use the batch operation {@link #executeAsync()} instead.
     */
    public function execute(): void;

    /**
     * Execute the migration asynchronously as batch. The returned batch
     * can be used to track the progress of the migration.
     *
     * @return BatchInterface the batch which executes the migration asynchronously.
     *
     * @throws AuthorizationException
     *   if the user has not all of the following permissions
     *   <ul>
     *     <li>Permissions#MIGRATE_INSTANCE permission on Resources#PROCESS_DEFINITION for source and target</li>
     *     <li>Permissions#CREATE or BatchPermissions#CREATE_BATCH_MIGRATE_PROCESS_INSTANCES permission on Resources#BATCH</li>
     *   </ul>
     */
    public function executeAsync(): BatchInterface;
}
