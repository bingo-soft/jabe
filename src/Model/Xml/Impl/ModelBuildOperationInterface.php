<?php

namespace Jabe\Model\Xml\Impl;

use Jabe\Model\Xml\ModelInterface;

interface ModelBuildOperationInterface
{
    public function performModelBuild(ModelInterface $model): void;
}
