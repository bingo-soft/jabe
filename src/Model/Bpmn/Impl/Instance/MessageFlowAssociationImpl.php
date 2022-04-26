<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
            BpmnModelConstants::BPMN_ATTRIBUTE_INNER_MESSAGE_FLOW_REF
        )
        ->required()
        ->qNameAttributeReference(MessageFlowInterface::class)
        ->build();

        self::$outerMessageFlowRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_OUTER_MESSAGE_FLOW_REF
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
