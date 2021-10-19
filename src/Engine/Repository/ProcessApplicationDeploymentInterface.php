<?php

namespace BpmPlatform\Engine\Repository;

use BpmPlatform\Engine\Application\ProcessApplicationRegistrationInterface;

interface ProcessApplicationDeploymentInterface
{
    public const PROCESS_APPLICATION_DEPLOYMENT_SOURCE = "process application";

    /**
     * @return the {@link ProcessApplicationRegistration} performed for this process application deployment.
     */
    public function getProcessApplicationRegistration(): ProcessApplicationRegistrationInterface;
}
