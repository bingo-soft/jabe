<?php

namespace Jabe\Runtime;

use Jabe\Authorization\{
    BatchPermissions,
    Permissions,
    Resources
};
use Jabe\Batch\BatchInterface;

interface ModificationBuilderInterface
{
    /**
     * <p><i>Submits the instruction:</i></p>
     *
     * <p>Cancel all instances of the given activity in an arbitrary order, which are:
     * <ul>
     *   <li>activity instances of that activity
     *   <li>transition instances entering or leaving that activity
     * </ul></p>
     *
     * <p>The cancellation order of the instances is arbitrary</p>
     *
     * @param activityId the activity for which all instances should be cancelled
     * @param cancelCurrentActiveActivityInstances
     */
    public function cancelAllForActivity(?string $activityId, ?bool $cancelCurrentActiveActivityInstances): ModificationBuilderInterface;

    /**
     * @param processInstanceIds the process instance ids to modify.
     */
    public function processInstanceIds(array $processInstanceIds): ModificationBuilderInterface;

    /**
     * @param processInstanceQuery a query which selects the process instances to modify.
     *   Query results are restricted to process instances for which the user has permission.
     */
    public function processInstanceQuery(ProcessInstanceQueryInterface $processInstanceQuery): ModificationBuilderInterface;

    /**
     * Skips custom execution listeners when creating/removing activity instances during modification
     */
    public function skipCustomListeners(): ModificationBuilderInterface;

    /**
     * Skips io mappings when creating/removing activity instances during modification
     */
    public function skipIoMappings(): ModificationBuilderInterface;

    /** Provides annotation for the current modification. */
    public function setAnnotation(?string $annotation): ModificationBuilderInterface;

    /**
     * Execute the modification synchronously.
     *
     * @throws AuthorizationException
     *   if the user has not all of the following permissions
     *   <ul>
     *      <li>if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE or no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION</li>
     *   </ul>
     * @throws BadUserRequestException
     *   When the affected instances count exceeds the maximum results limit. A maximum results
     *   limit can be specified with the process engine configuration property
     *   <code>queryMaxResultsLimit</code> (default Integer#MAX_VALUE).
     *   Please use the batch operation {@link #executeAsync()} instead.
     */
    public function execute(): void;

    /**
     * Execute the modification asynchronously as batch. The returned batch
     * can be used to track the progress of the modification.
     *
     * @return BatchInterface the batch which executes the modification asynchronously.
     *
     * @throws AuthorizationException
     *   if the user has not all of the following permissions
     *   <ul>
     *     <li>Permissions#CREATE or BatchPermissions#CREATE_BATCH_MODIFY_PROCESS_INSTANCES permission on Resources#BATCH</li>
     *   </ul>
     */
    public function executeAsync(): BatchInterface;
}
