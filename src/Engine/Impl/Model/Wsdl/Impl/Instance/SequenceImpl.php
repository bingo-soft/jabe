<?php

namespace Jabe\Engine\Impl\Model\Wsdl\Impl\Instance;

use Xml\ModelBuilder;
use Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Xml\Instance\ModelElementInstanceInterface;
use Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Engine\Impl\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Engine\Impl\Model\Wsdl\Instance\{
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
