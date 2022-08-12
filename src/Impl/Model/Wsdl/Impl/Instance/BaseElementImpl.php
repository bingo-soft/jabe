<?php

namespace Jabe\Impl\Model\Wsdl\Impl\Instance;

use Xml\ModelBuilder;
use Jabe\Impl\Model\Wsdl\Impl\WsdlModelConstants;
use Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Impl\Model\Wsdl\Instance\BaseElementInterface;

abstract class BaseElementImpl extends WsdlModelElementInstanceImpl implements BaseElementInterface
{

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BaseElementInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_BASE_ELEMENT
        )
        ->namespaceUri(WsdlModelConstants::WSDL_NS)
        ->abstractType();

        $typeBuilder->build();
    }
}
