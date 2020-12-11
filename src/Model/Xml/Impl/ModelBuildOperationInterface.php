<?php

namespace BpmPlatform\Model\Xml\Impl;

use BpmPlatform\Model\Xml\ModelInterface;

interface ModelBuildOperationInterface
{
    public function performModelBuild(ModelInterface $model): void;
}
