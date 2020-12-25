<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    ErrorInterface,
    MessageInterface,
    OperationInterface
};

class OperationImpl extends BaseElementImpl implements OperationInterface
{
    protected static $nameAttribute;
    protected static $implementationRefAttribute;
    protected static $inMessageRefChild;
    protected static $outMessageRefChild;
    protected static $errorRefCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            OperationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_OPERATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new OperationImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BaseElementInterface::BPMN_ATTRIBUTE_NAME)
        ->required()
        ->build();

        self::$implementationRefAttribute = $typeBuilder->stringAttribute(
            BaseElementInterface::BPMN_ELEMENT_IMPLEMENTATION_REF
        )
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$inMessageRefChild = $sequenceBuilder->element(InMessageRef::class)
        ->required()
        ->qNameElementReference(MessageInterface::class)
        ->build();

        self::$outMessageRefChild = $sequenceBuilder->element(OutMessageRef::class)
        ->qNameElementReference(MessageInterface::class)
        ->build();

        self::$errorRefCollection = $sequenceBuilder->elementCollection(ErrorRef::class)
        ->qNameElementReferenceCollection(ErrorInterface::class)
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

    public function getInMessage(): MessageInterface
    {
        return self::$inMessageRefChild->getReferenceTargetElement($this);
    }

    public function setInMessage(MessageInterface $message): void
    {
        self::$inMessageRefChild->setReferenceTargetElement($this, $message);
    }

    public function getOutMessage(): MessageInterface
    {
        return self::$outMessageRefChild->getReferenceTargetElement($this);
    }

    public function setOutMessage(MessageInterface $message): void
    {
        self::$outMessageRefChild->setReferenceTargetElement($this, $message);
    }

    public function getErrors(): array
    {
        return self::$errorRefCollection->getReferenceTargetElements($this);
    }
}
