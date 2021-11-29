<?php

namespace BpmPlatform\Engine\Application;

interface ProcessApplicationDeploymentInfoInterface
{
    /**
     * @return the name of the process engine the deployment was made to
     */
    public function getProcessEngineName(): string;

    /**
     * @return the id of the deployment that was performed.
     */
    public function getDeploymentId(): ?string;
}
