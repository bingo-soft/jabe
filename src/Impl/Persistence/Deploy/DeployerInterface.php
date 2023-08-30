<?php

namespace Jabe\Impl\Persistence\Deploy;

use Jabe\Impl\Persistence\Entity\DeploymentEntity;

interface DeployerInterface
{
    public function deploy(DeploymentEntity $deployment): void;
}
