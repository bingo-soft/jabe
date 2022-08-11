<?php

namespace Jabe\Engine\Impl\Model\Wsdl\Impl\Instance;

use Xml\ModelBuilder;
use Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Xml\Instance\ModelElementInstanceInterface;
use Jabe\Engine\Impl\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Engine\Impl\Model\Wsdl\Instance\{
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
