<?php

namespace Jabe\Model\Wsdl\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Model\Wsdl\Instance\{
    RootElementInterface,
    SchemaInterface,
    TypesInterface
};

class TypesImpl extends RootElementImpl implements TypesInterface
{
    protected static $schemaChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            TypesInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_TYPES
        )
        ->namespaceUri(WsdlModelConstants::WSDL_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new TypesImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$schemaChild = $sequenceBuilder->element(SchemaInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getSchema(): SchemaInterface
    {
        return self::$schemaChild->getChild($this);
    }

    public function setSchema(SchemaInterface $schema): void
    {
        self::$schemaChild->setChild($this, $schema);
    }
}
