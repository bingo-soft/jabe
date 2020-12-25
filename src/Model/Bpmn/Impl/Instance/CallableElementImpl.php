<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    CallableElementInterface,
    InterfaceInterface,
    IoBindingInterface,
    IoSpecificationInterface
};

class CallableElementImpl extends RootElementImpl implements CallableElementInterface
{
    protected static $nameAttribute;
    protected static $supportedInterfaceRefCollection;
    protected static $ioSpecificationChild;
    protected static $ioBindingCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $bpmnModelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CallableElementInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CALLABLE_ELEMENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CallableElementImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$supportedInterfaceRefCollection = $sequenceBuilder->elementCollection(SupportedInterfaceRef::class)
        ->qNameElementReferenceCollection(InterfaceInterface::class)
        ->build();

        self::$ioSpecificationChild = $sequenceBuilder->element(IoSpecificationInterface::class)
        ->build();

        self::$ioBindingCollection = $sequenceBuilder->elementCollection(IoBindingInterface::class)
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

    public function getSupportedInterfaces(): array
    {
        return self::$supportedInterfaceRefCollection->getReferenceTargetElements($this);
    }

    public function getIoSpecification(): IoSpecificationInterface
    {
        return self::$ioSpecificationChild->getChild($this);
    }

    public function setIoSpecification(IoSpecificationInterface $ioSpecification): void
    {
        self::$ioSpecificationChild->setChild($this, $ioSpecification);
    }

    public function getIoBindings(): array
    {
        return self::$ioBindingCollection->get($this);
    }
}
