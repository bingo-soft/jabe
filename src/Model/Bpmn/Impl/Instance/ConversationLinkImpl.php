<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    ConversationLinkInterface,
    InteractionNodeInterface
};

class ConversationLinkImpl extends BaseElementImpl implements ConversationLinkInterface
{
    protected static $nameAttribute;
    protected static $sourceRefAttribute;
    protected static $targetRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ConversationLinkInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CONVERSATION_LINK
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ConversationLinkImpl($instanceContext);
                }
            }
        );

        self::$sourceRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_SOURCE_REF)
        ->required()
        ->qNameAttributeReference(InteractionNodeInterface::class)
        ->build();

        self::$targetRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_TARGET_REF)
        ->required()
        ->qNameAttributeReference(InteractionNodeInterface::class)
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
}
