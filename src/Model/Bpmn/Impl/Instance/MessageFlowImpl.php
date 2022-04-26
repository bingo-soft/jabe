<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    BaseElementInterface,
    InteractionNodeInterface,
    MessageInterface,
    MessageFlowInterface
};
use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

class MessageFlowImpl extends BaseElementImpl implements MessageFlowInterface
{
    protected static $nameAttribute;
    protected static $sourceRefAttribute;
    protected static $targetRefAttribute;
    protected static $messageRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            MessageFlowInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_MESSAGE_FLOW
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new MessageFlowImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$sourceRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_SOURCE_REF)
        ->required()
        ->qNameAttributeReference(InteractionNodeInterface::class)
        ->build();

        self::$targetRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_TARGET_REF)
        ->required()
        ->qNameAttributeReference(InteractionNodeInterface::class)
        ->build();

        self::$messageRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_MESSAGE_REF)
        ->qNameAttributeReference(MessageInterface::class)
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

    public function getSource(): InteractionNodeInterface
    {
        return self::$sourceRefAttribute->getReferenceTargetElement($this);
    }

    public function setSource(InteractionNodeInterface $source): void
    {
        self::$sourceRefAttribute->setReferenceTargetElement($this, $source);
    }

    public function getTarget(): InteractionNodeInterface
    {
        return self::$targetRefAttribute->getReferenceTargetElement($this);
    }

    public function setTarget(InteractionNodeInterface $target): void
    {
        self::$targetRefAttribute->setReferenceTargetElement($this, $target);
    }

    public function getMessage(): MessageInterface
    {
        return self::$messageRefAttribute->getReferenceTargetElement($this);
    }

    public function setMessage(MessageInterface $message): void
    {
        self::$messageRefAttribute->setReferenceTargetElement($this, $message);
    }

    public function getDiagramElement(): BpmnEdgeInterface
    {
        return parent::getDiagramElement();
    }
}
