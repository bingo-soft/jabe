<?php

namespace Jabe\Application;

interface ProcessApplicationDeploymentInfoInterface
{
    /**
     * @return string the name of the process engine the deployment was made to
     */
    public function getProcessEngineName(): ?string;

    /**
     * @return string the id of the deployment that was performed.
     */
    public function getDeploymentId(): ?string;
}
