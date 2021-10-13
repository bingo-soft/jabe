<?php

namespace BpmPlatform\Engine\Repository;

interface CandidateDeploymentInterface
{
    public function getName(): string;

    /**
     * @return a map of all the resources provided for deployment
     */
    public function getResources(): array;
}
