<?php

namespace Jabe\Model\Wsdl\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Model\Wsdl\Instance\{
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

    public function getLocation(): string
    {
        return self::$locationAttribute->getValue($this);
    }

    public function setLocation(string $location): void
    {
        self::$locationAttribute->setValue($this, $location);
    }
}
