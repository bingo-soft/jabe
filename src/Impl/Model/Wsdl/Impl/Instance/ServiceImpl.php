<?php

namespace Jabe\Impl\Model\Wsdl\Impl\Instance;

use Xml\ModelBuilder;
use Xml\Instance\ModelElementInstanceInterface;
use Xml\Impl\Instance\ModelTypeInstanceContext;
use Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Impl\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Impl\Model\Wsdl\Instance\{
    PortInterface,
    RootElementInterface,
    ServiceInterface
};

class ServiceImpl extends RootElementImpl implements ServiceInterface
{
    protected static $nameAttribute;
    protected static $portChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ServiceInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_SERVICE
        )
        ->namespaceUri(WsdlModelConstants::WSDL_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ServiceImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::WSDL_ATTRIBUTE_NAME)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$portChild = $sequenceBuilder->element(PortInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getName(): ?string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getPort(): PortInterface
    {
        return self::$portChild->getChild($this);
    }

    public function setPort(PortInterface $port): void
    {
        self::$portChild->setChild($this, $port);
    }

    public function getBinding(): string
    {
        return $this->getPort()->getBinding();
    }

    public function getEndpoint(): string
    {
        return $this->getPort()->getAddress()->getLocation();
    }
}
