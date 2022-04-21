<?php

namespace BpmPlatform\Model\Wsdl\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Wsdl\Impl\WsdlModelConstants;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Wsdl\Instance\BaseElementInterface;

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
