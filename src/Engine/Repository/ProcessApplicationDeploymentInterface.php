<?php

namespace Jabe\Engine\Repository;

use Jabe\Engine\Application\ProcessApplicationRegistrationInterface;

interface ProcessApplicationDeploymentInterface
{
    public const PROCESS_APPLICATION_DEPLOYMENT_SOURCE = "process application";

    /**
     * @return ProcessApplicationRegistrationInterface the {@link ProcessApplicationRegistration} performed for this process application deployment.
     */
    public function getProcessApplicationRegistration(): ProcessApplicationRegistrationInterface;
}
