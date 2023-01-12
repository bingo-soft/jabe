<?php

namespace Jabe\Impl\Pvm;

use Jabe\Impl\Pvm\Process\ActivityImpl;

interface PvmProcessDefinitionInterface extends ReadOnlyProcessDefinitionInterface
{
    public function getDeploymentId(): ?string;

    public function createProcessInstance(?string $businessKey = null, ?string $caseInstanceId = null, ?ActivityImpl $initial = null): PvmProcessInstanceInterface;
}
