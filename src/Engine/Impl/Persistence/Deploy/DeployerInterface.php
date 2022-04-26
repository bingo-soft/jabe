<?php

namespace Jabe\Engine\Impl\Persistence\Deploy;

use Jabe\Engine\Impl\Persistence\Entity\DeploymentEntity;

interface DeployerInterface
{
    public function deploy(DeploymentEntity $deployment): void;
}
