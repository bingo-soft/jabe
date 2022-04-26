<?php

namespace Jabe\Model\Wsdl\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Model\Wsdl\Instance\{
    BaseElementInterface,
    RootElementInterface
};

class RootElementImpl extends BaseElementImpl implements RootElementInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            RootElementInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_ROOT_ELEMENT
        )
        ->namespaceUri(WsdlModelConstants::WSDL_NS)
        ->extendsType(BaseElementInterface::class)
        ->abstractType();

        $typeBuilder->build();
    }
}
