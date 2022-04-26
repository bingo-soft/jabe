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
    ElementInterface,
    SequenceInterface
};

class SequenceImpl extends ModelElementInstanceImpl implements SequenceInterface
{
    protected static $elementCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            SequenceInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_SEQUENCE
        )
        ->namespaceUri(WsdlModelConstants::XS_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new SequenceImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$elementCollection = $sequenceBuilder->elementCollection(ElementInterface::class)
        ->minOccurs(0)
        ->build();

        $typeBuilder->build();
    }

    public function getElements(): array
    {
        return self::$elementCollection->get($this);
    }
}
