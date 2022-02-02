<?php

namespace BpmPlatform\Engine\Impl\Persistence\Deploy;

use BpmPlatform\Engine\Impl\Persistence\Entity\DeploymentEntity;

interface DeployerInterface
{
    public function deploy(DeploymentEntity $deployment): void;
}
