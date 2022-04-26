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
    BaseElementInterface,
    ComplexTypeInterface,
    SequenceInterface
};

class ComplexTypeImpl extends ModelElementInstanceImpl implements ComplexTypeInterface
{
    protected static $nameAttribute;
    protected static $sequenceChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ComplexTypeInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_COMPLEX_TYPE
        )
        ->namespaceUri(WsdlModelConstants::XS_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ComplexTypeImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::WSDL_ATTRIBUTE_NAME)
        ->required()
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$sequenceChild = $sequenceBuilder->element(SequenceInterface::class)
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

    public function getParameters(): array
    {
        return self::$sequenceChild->getChild($this)->getElements();
    }
}
