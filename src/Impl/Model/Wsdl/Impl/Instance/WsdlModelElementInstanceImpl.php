<?php

namespace Jabe\Impl\Model\Wsdl\Impl\Instance;

use Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Impl\Model\Wsdl\Instance\WsdlModelElementInstanceInterface;

abstract class WsdlModelElementInstanceImpl extends ModelElementInstanceImpl implements WsdlModelElementInstanceInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }
}
