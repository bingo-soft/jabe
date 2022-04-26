<?php

namespace Jabe\Model\Wsdl\Impl\Instance;

use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Wsdl\Instance\WsdlModelElementInstanceInterface;

abstract class WsdlModelElementInstanceImpl extends ModelElementInstanceImpl implements WsdlModelElementInstanceInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }
}
