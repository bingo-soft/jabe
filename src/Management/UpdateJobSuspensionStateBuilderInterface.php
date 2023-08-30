<?php

namespace Jabe\Management;

interface UpdateJobSuspensionStateBuilderInterface
{
    /**
     * Activates the provided jobs.
     *
     * @throws AuthorizationException
     *           if the user has no Permissions#UPDATE permission on
     *           Resources#PROCESS_INSTANCE or no
     *           Permissions#UPDATE_INSTANCE permission on
     *           Resources#PROCESS_DEFINITION.
     */
    public function activate(): void;

    /**
     * Suspends the provided jobs. If a job is in state suspended, it will not be
     * executed by the job executor.
     *
     * @throws AuthorizationException
     *           if the user has no Permissions#UPDATE permission on
     *           Resources#PROCESS_INSTANCE or no
     *           Permissions#UPDATE_INSTANCE permission on
     *           Resources#PROCESS_DEFINITION.
     */
    public function suspend(): void;
}
