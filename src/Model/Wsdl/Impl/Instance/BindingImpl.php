<?php

namespace BpmPlatform\Model\Wsdl\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Wsdl\Impl\WsdlModelConstants;
use BpmPlatform\Model\Wsdl\Instance\{
    BindingInterface,
    OperationInterface,
    RootElementInterface
};

class BindingImpl extends RootElementImpl implements BindingInterface
{
    protected static $nameAttribute;
    protected static $typeAttribute;
    protected static $operationCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BindingInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_BINDING
        )
        ->namespaceUri(WsdlModelConstants::WSDL_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new BindingImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::WSDL_ATTRIBUTE_NAME)
        ->build();

        self::$typeAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::WSDL_ATTRIBUTE_TYPE)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$operationCollection = $sequenceBuilder->elementCollection(OperationInterface::class)
        ->minOccurs(0)
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

    public function getType(): ?string
    {
        return self::$typeAttribute->getValue($this);
    }

    public function setType(string $type): void
    {
        self::$typeAttribute->setValue($this, $type);
    }

    public function getOperations(): array
    {
        return self::$operationCollection->get($this);
    }
}
