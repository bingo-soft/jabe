<?php

namespace Jabe\Engine\Impl\Model\Wsdl\Impl\Instance;

use Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Engine\Impl\Model\Wsdl\Instance\WsdlModelElementInstanceInterface;

abstract class WsdlModelElementInstanceImpl extends ModelElementInstanceImpl implements WsdlModelElementInstanceInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }
}
