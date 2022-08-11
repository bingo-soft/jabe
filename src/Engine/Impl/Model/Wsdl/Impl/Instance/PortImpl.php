<?php

namespace Jabe\Engine\Impl\Model\Wsdl\Impl\Instance;

use Xml\ModelBuilder;
use Xml\Impl\Instance\ModelTypeInstanceContext;
use Xml\Instance\ModelElementInstanceInterface;
use Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Engine\Impl\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Engine\Impl\Model\Wsdl\Instance\{
    AddressInterface,
    BaseElementInterface,
    PortInterface
};

class PortImpl extends BaseElementImpl implements PortInterface
{
    protected static $nameAttribute;
    protected static $bindingAttribute;
    protected static $addressChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            PortInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_PORT
        )
        ->namespaceUri(WsdlModelConstants::WSDL_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new PortImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::WSDL_ATTRIBUTE_NAME)
        ->required()
        ->build();

        self::$bindingAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::WSDL_ATTRIBUTE_BINDING)
        ->required()
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$addressChild = $sequenceBuilder->element(AddressInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getBinding(): string
    {
        return self::$bindingAttribute->getValue($this);
    }

    public function setBinding(string $binding): void
    {
        self::$bindingAttribute->setValue($this, $binding);
    }

    public function getAddress(): AddressInterface
    {
        return self::$addressChild->getChild($this);
    }

    public function setAddress(AddressInterface $address): void
    {
        self::$addressChild->setChild($this, $address);
    }
}
