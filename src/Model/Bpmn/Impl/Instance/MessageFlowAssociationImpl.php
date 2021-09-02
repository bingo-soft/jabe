<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    MessageFlowAssociationInterface,
    MessageFlowInterface
};

class MessageFlowAssociationImpl extends BaseElementImpl implements MessageFlowAssociationInterface
{
    protected static $innerMessageFlowRefAttribute;
    protected static $outerMessageFlowRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            MessageFlowAssociationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_MESSAGE_FLOW_ASSOCIATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new MessageFlowAssociationImpl($instanceContext);
                }
            }
        );

        self::$innerMessageFlowRefAttribute = $typeBuilder->stringAttribute(
            BaseElementInterface::BPMN_ATTRIBUTE_INNER_MESSAGE_FLOW_REF
        )
        ->required()
        ->qNameAttributeReference(MessageFlowInterface::class)
        ->build();

        self::$outerMessageFlowRefAttribute = $typeBuilder->stringAttribute(
            BaseElementInterface::BPMN_ATTRIBUTE_OUTER_MESSAGE_FLOW_REF
        )
        ->required()
        ->qNameAttributeReference(MessageFlowInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getInnerMessageFlow(): MessageFlowInterface
    {
        return self::$innerMessageFlowRefAttribute->getReferenceTargetElement($this);
    }

    public function setInnerMessageFlow(MessageFlowInterface $innerMessageFlow): void
    {
        self::$innerMessageFlowRefAttribute->setReferenceTargetElement($this, $innerMessageFlow);
    }

    public function getOuterMessageFlow(): MessageFlowInterface
    {
        return self::$outerMessageFlowRefAttribute->getReferenceTargetElement($this);
    }

    public function setOuterMessageFlow(MessageFlowInterface $outerMessageFlow): void
    {
        self::$outerMessageFlowRefAttribute->setReferenceTargetElement($this, $outerMessageFlow);
    }
}
