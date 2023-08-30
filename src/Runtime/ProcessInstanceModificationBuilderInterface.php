<?php

namespace Jabe\Runtime;

use Jabe\Authorization\{
    BatchPermissions,
    Permissions,
    Resources
};
use Jabe\Batch\BatchInterface;

interface ProcessInstanceModificationBuilderInterface extends InstantiationBuilderInterface
{
    /**
     * <p><i>Submits the instruction:</i></p>
     *
     * <p>Start before the specified activity. Instantiate the given activity
     * as a descendant of the given ancestor activity instance.</p>
     *
     * <p>In particular:
     *   <ul>
     *     <li>Instantiate all activities between the ancestor activity and the activity to execute</li>
     *     <li>Instantiate and execute the given activity (respects the asyncBefore
     *       attribute of the activity)</li>
     *   </ul>
     * </p>
     *
     * @param activityId the activity to instantiate
     * @param ancestorActivityInstanceId the ID of an existing activity instance under which the new
     *   activity instance should be created
     */
    public function startBeforeActivity(
        ?string $activityId,
        ?string $ancestorActivityInstanceId = null
    ): ProcessInstanceModificationBuilderInterface;

    /**
     * Submits an instruction that behaves like {@link #startTransition(String,String)} and always instantiates
     * the single outgoing sequence flow of the given activity. Does not consider asyncAfter.
     *
     * @param activityId the activity for which the outgoing flow should be executed
     * @throws ProcessEngineException if the activity has 0 or more than 1 outgoing sequence flows
     */
    public function startAfterActivity(
        ?string $activityId,
        ?string $ancestorActivityInstanceId = null
    ): ProcessInstanceModificationBuilderInterface;

    /**
     * <p><i>Submits the instruction:</i></p>
     *
     * <p>Start the specified sequence flow. Instantiate the given sequence flow
     * as a descendant of the given ancestor activity instance.</p>
     *
     * <p>In particular:
     *   <ul>
     *     <li>Instantiate all activities between the ancestor activity and the activity to execute</li>
     *     <li>Execute the given transition (does not consider sequence flow conditions)</li>
     *   </ul>
     * </p>
     *
     * @param transitionId the sequence flow to execute
     * @param ancestorActivityInstanceId the ID of an existing activity instance under which the new
     *   transition should be executed
     */
    public function startTransition(
        ?string $transitionId,
        ?string $ancestorActivityInstanceId = null
    ): ProcessInstanceModificationBuilderInterface;

    /**
     * <p><i>Submits the instruction:</i></p>
     *
     * <p>Cancel an activity instance in a process. If this instance has child activity instances
     * (e.g. in a subprocess instance), these children, their grandchildren, etc. are cancelled as well.</p>
     *
     * <p>Process instance cancellation will propagate upward, removing any parent process instances that are
     * only waiting on the cancelled process to complete.</p>
     *
     * @param activityInstanceId the id of the activity instance to cancel
     */
    public function cancelActivityInstance(?string $activityInstanceId): ProcessInstanceModificationBuilderInterface;

    /**
     * <p><i>Submits the instruction:</i></p>
     *
     * <p>Cancel a transition instance (i.e. an async continuation) in a process.</p>
     *
     * @param transitionInstanceId the id of the transition instance to cancel
     */
    public function cancelTransitionInstance(?string $transitionInstanceId): ProcessInstanceModificationBuilderInterface;

    /**
     * <p><i>Submits the instruction:</i></p>
     *
     * <p>Cancel all instances of the given activity in an arbitrary order, which are:
     * <ul>
     *   <li>activity instances of that activity
     *   <li>transition instances entering or leaving that activity
     * </ul></p>
     *
     * <p>Therefore behaves like {@link #cancelActivityInstance(String)} for each individual
     * activity instance and like {@link #cancelTransitionInstance(String)} for each
     * individual transition instance.</p>
     *
     * <p>The cancellation order of the instances is arbitrary</p>
     *
     * @param activityId the activity for which all instances should be cancelled
     */
    public function cancelAllForActivity(?string $activityId): ProcessInstanceModificationBuilderInterface;

    /**
     * <p>
     * A canceled process instance receives a termination state to indicate the
     * source of the cancellation call. The state can have the following values:
     *   <ul>
     *     <li><code>EXTERNALLY_TERMINATED</code>: the cancellation was triggered by
     * an external source. (e.g. REST call, external application)</li>
     *     <li><code>INTERNALLY_TERMINATED</code>: the cancellation was triggered
     * internally. (e.g. by the engine)</li>
     *   </ul>
     * </p>
     *
     * @param external
     *          was the cancellation triggered by an external source?
     *          <code>true</code> for <code>EXTERNALLY_TERMINATED</code>,
     *          <code>false</code> for <code>INTERNALLY_TERMINATED</code>.
     */
    public function cancellationSourceExternal(bool $external): ProcessInstanceModificationBuilderInterface;

    /** Provides annotation for the current modification. */
    public function setAnnotation(?string $annotation): ProcessInstanceModificationBuilderInterface;

    /**
     * @param skipCustomListeners specifies whether custom listeners (task and execution)
     *   should be invoked when executing the instructions
     * @param skipIoMappings specifies whether input/output mappings for tasks should be invoked
     *   throughout the transaction when executing the instructions
     *
     * @throws AuthorizationException
     *          if the user has no Permissions#UPDATE permission on Resources#PROCESS_INSTANCE
     *          or no Permissions#UPDATE_INSTANCE permission on Resources#PROCESS_DEFINITION.
     *          if the process instance will be delete and the user has no Permissions#DELETE permission
     *          on Resources#PROCESS_INSTANCE or no Permissions#DELETE_INSTANCE permission on
     *          Resources#PROCESS_DEFINITION.
     */
    public function execute(
        ?bool $writeUserOperationLog = true,
        ?bool $skipCustomListeners = false,
        ?bool $skipIoMappings = false
    ): void;

    /**
     * @param skipCustomListeners specifies whether custom listeners (task and execution)
     *   should be invoked when executing the instructions
     * @param skipIoMappings specifies whether input/output mappings for tasks should be invoked
     *   throughout the transaction when executing the instructions
     *
     * @throws AuthorizationException
     *               if the user has no Permissions#CREATE or
     *               BatchPermissions#CREATE_BATCH_MODIFY_PROCESS_INSTANCES permission on Resources#BATCH.
     *
     * @return a batch job to be executed by the executor
     */
    public function executeAsync(bool $skipCustomListeners, bool $skipIoMappings): BatchInterface;
}
