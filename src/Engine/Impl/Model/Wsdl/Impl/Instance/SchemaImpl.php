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
    ComplexTypeInterface,
    SchemaInterface
};

class SchemaImpl extends ModelElementInstanceImpl implements SchemaInterface
{
    protected static $elementCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            SchemaInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_SCHEMA
        )
        ->namespaceUri(WsdlModelConstants::XS_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new SchemaImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$elementCollection = $sequenceBuilder->elementCollection(ComplexTypeInterface::class)
        ->minOccurs(0)
        ->build();

        $typeBuilder->build();
    }

    public function getElements(): array
    {
        return self::$elementCollection->get($this);
    }

    //@TODO - getImport dynamically
}
