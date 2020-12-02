<?php

namespace BpmPlatform\Model\Xml\Type;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;

interface ModelTypeInstanceProviderInterface
{
    public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface;
}
