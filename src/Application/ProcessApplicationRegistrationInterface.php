<?php

namespace Jabe\Application;

interface ProcessApplicationRegistrationInterface
{
    public function getDeploymentIds(): array;

    /**
     * @return string the name of the process engine to which the deployment was made
     */
    public function getProcessEngineName(): string;
}
