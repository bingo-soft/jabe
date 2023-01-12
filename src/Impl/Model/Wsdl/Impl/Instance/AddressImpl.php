<?php

namespace Jabe\Impl\Model\Wsdl\Impl\Instance;

use Xml\ModelBuilder;
use Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Xml\Instance\ModelElementInstanceInterface;
use Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Impl\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Impl\Model\Wsdl\Instance\{
    AddressInterface,
    BaseElementInterface
};

class AddressImpl extends ModelElementInstanceImpl implements AddressInterface
{
    protected static $locationAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            AddressInterface::class,
            WsdlModelConstants::SOAP_ELEMENT_ADDRESS
        )
        ->namespaceUri(WsdlModelConstants::SOAP_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new AddressImpl($instanceContext);
                }
            }
        );

        self::$locationAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::SOAP_ATTRIBUTE_LOCATION)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getLocation(): ?string
    {
        return self::$locationAttribute->getValue($this);
    }

    public function setLocation(?string $location): void
    {
        self::$locationAttribute->setValue($this, $location);
    }
}
