<?php

namespace BpmPlatform\Model\Wsdl\Impl\Instance;

use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Wsdl\Instance\WsdlModelElementInstanceInterface;

abstract class WsdlModelElementInstanceImpl extends ModelElementInstanceImpl implements WsdlModelElementInstanceInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }
}
