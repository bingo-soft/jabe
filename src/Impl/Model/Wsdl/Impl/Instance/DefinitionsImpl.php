<?php

namespace Jabe\Impl\Model\Wsdl\Impl\Instance;

use Xml\ModelBuilder;
use Xml\Instance\ModelElementInstanceInterface;
use Xml\Impl\Instance\{
    ModelTypeInstanceContext,
    ModelElementInstanceImpl
};
use Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Impl\Model\Wsdl\Impl\WsdlModelConstants;
use Jabe\Impl\Model\Wsdl\Instance\{
    BindingInterface,
    DefinitionsInterface,
    RootElementInterface,
    TypesInterface
};

class DefinitionsImpl extends ModelElementInstanceImpl implements DefinitionsInterface
{
    protected static $nameAttribute;
    protected static $targetNamespaceAttribute;
    protected static $rootElementCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DefinitionsInterface::class,
            WsdlModelConstants::WSDL_ELEMENT_DEFINITIONS
        )
        ->namespaceUri(WsdlModelConstants::WSDL_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DefinitionsImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(WsdlModelConstants::WSDL_ATTRIBUTE_NAME)
        ->build();

        self::$targetNamespaceAttribute = $typeBuilder->stringAttribute(
            WsdlModelConstants::WSDL_ATTRIBUTE_TARGET_NAMESPACE
        )
        ->required()
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$rootElementCollection = $sequenceBuilder->elementCollection(RootElementInterface::class)
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

    public function getTargetNamespace(): string
    {
        return self::$targetNamespaceAttribute->getValue($this);
    }

    public function setTargetNamespace(string $namespace): void
    {
        self::$targetNamespaceAttribute->setValue($this, $namespace);
    }

    public function getRootElements(): array
    {
        return self::$rootElementCollection->get($this);
    }

    public function addRootElement(RootElementInterface $element): void
    {
        self::$rootElementCollection->add($this, $element);
    }

    public function removeRootElement(RootElementInterface $element): void
    {
        self::$rootElementCollection->remove($this, $element);
    }

    public function getTypes(): ?TypesInterface
    {
        foreach (self::$rootElementCollection->get($this) as $child) {
            if ($child instanceof TypesInterface) {
                return $child;
            }
        }
        return null;
    }

    public function getBindings(): array
    {
        $arr = [];
        foreach (self::$rootElementCollection->get($this) as $child) {
            if ($child instanceof BindingInterface) {
                $arr[] = $child;
            }
        }
        return $arr;
    }
}
