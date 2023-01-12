<?php

namespace Jabe\Repository;

interface ProcessDefinitionInterface extends ResourceDefinitionInterface
{
    /** description of this process **/
    public function getDescription(): ?string;

    /** Does this process definition has a {@link FormService#getStartFormData(String) start form key}. */
    public function hasStartFormKey(): bool;

    /** Returns true if the process definition is in suspended state. */
    public function isSuspended(): bool;

    /** Version tag of the process definition. */
    public function getVersionTag(): ?string;

    /** Returns true if the process definition is startable in Tasklist. */
    public function isStartableInTasklist(): bool;
}
