<?php

namespace Jabe\Impl\Model\Wsdl\Impl\Instance;

use Xml\ModelBuilder;
use Xml\Impl\Instance\ModelTypeInstanceContext;
use Xml\Instance\ModelElementInstanceInterface;
use Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Impl\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Impl\Model\Wsdl\Instance\{
    BaseElementInterface,
    OperationInterface
};

class OperationImpl extends BaseElementImpl implements OperationInterface
{
    protected static $nameAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            OperationInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_OPERATION
        )
        ->namespaceUri(WsdlModelConstants::WSDL_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new OperationImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::WSDL_ATTRIBUTE_NAME)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getName(): ?string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(?string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }
}
