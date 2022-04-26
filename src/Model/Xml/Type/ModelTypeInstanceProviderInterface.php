<?php

namespace Jabe\Model\Xml\Type;

use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;

interface ModelTypeInstanceProviderInterface
{
    public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface;
}
