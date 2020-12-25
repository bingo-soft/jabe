<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    InterfaceInterface,
    OperationInterface,
    RootElementInterface
};

class InterfaceImpl extends RootElementImpl implements InterfaceInterface
{
    protected static $nameAttribute;
    protected static $implementationRefAttribute;
    protected static $operationCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            InterfaceInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_INTERFACE
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new InterfaceImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->required()
        ->build();

        self::$implementationRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_IMPLEMENTATION_REF
        )
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$operationCollection = $sequenceBuilder->elementCollection(OperationInterface::class)
        ->required()
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

    public function getImplementationRef(): string
    {
        return self::$implementationRefAttribute->getValue($this);
    }

    public function setImplementationRef(string $implementationRef): void
    {
        self::$implementationRefAttribute->setValue($this, $implementationRef);
    }

    public function getOperations(): array
    {
        return self::$operationCollection->get($this);
    }
}
