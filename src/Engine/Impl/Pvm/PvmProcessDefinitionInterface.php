<?php

namespace BpmPlatform\Engine\Impl\Pvm;

interface PvmProcessDefinitionInterface extends ReadOnlyProcessDefinitionInterface
{
    public function getDeploymentId(): string;

    public function createProcessInstance(?string $businessKey = null, ?string $caseInstanceId = null): PvmProcessInstanceInterface;
}
