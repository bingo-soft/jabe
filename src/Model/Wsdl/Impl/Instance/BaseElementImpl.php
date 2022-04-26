<?php

namespace Jabe\Model\Wsdl\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Wsdl\Instance\BaseElementInterface;

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
