<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    BaseElementInterface,
    ConversationAssociationInterface,
    ConversationNodeInterface
};

class ConversationAssociationImpl extends BaseElementImpl implements ConversationAssociationInterface
{
    protected static $innerConversationNodeRefAttribute;
    protected static $outerConversationNodeRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ConversationAssociationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CONVERSATION_ASSOCIATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ConversationAssociationImpl($instanceContext);
                }
            }
        );

        self::$innerConversationNodeRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_INNER_CONVERSATION_NODE_REF
        )
        ->required()
        ->qNameAttributeReference(ConversationNodeInterface::class)
        ->build();

        self::$outerConversationNodeRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_OUTER_CONVERSATION_NODE_REF
        )
        ->required()
        ->qNameAttributeReference(ConversationNodeInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getInnerConversationNode(): ConversationNodeInterface
    {
        return self::$innerConversationNodeRefAttribute->getReferenceTargetElement($this);
    }

    public function setInnerConversationNode(ConversationNodeInterface $innerConversationNode): void
    {
        self::$innerConversationNodeRefAttribute->setReferenceTargetElement($this, $innerConversationNode);
    }

    public function getOuterConversationNode(): ConversationNodeInterface
    {
        return self::$outerConversationNodeRefAttribute->getReferenceTargetElement($this);
    }

    public function setOuterConversationNode(ConversationNodeInterface $outerConversationNode): void
    {
        self::$outerConversationNodeRefAttribute->setReferenceTargetElement($this, $outerConversationNode);
    }
}
