<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    CorrelationPropertyRetrievalExpressionInterface,
    MessageInterface
};

class CorrelationPropertyRetrievalExpressionImpl extends BaseElementImpl implements CorrelationPropertyRetrievalExpressionInterface
{
    protected static $messageRefAttribute;
    protected static $messagePathChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CorrelationPropertyRetrievalExpressionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CORRELATION_PROPERTY_RETRIEVAL_EXPRESSION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CorrelationPropertyRetrievalExpressionImpl($instanceContext);
                }
            }
        );

        self::$messageRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_MESSAGE_REF)
        ->required()
        ->qNameAttributeReference(MessageInterface::class)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$messagePathChild = $sequenceBuilder->element(MessagePath::class)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getMessage(): MessageInterface
    {
        return self::$messageRefAttribute->getReferenceTargetElement($this);
    }

    public function setMessage(MessageInterface $message): void
    {
        self::$messageRefAttribute->setReferenceTargetElement($this, $message);
    }

    public function getMessagePath(): MessagePath
    {
        return self::$messagePathChild->getChild($this);
    }

    public function setMessagePath(MessagePath $messagePath): void
    {
        self::$messagePathChild->setChild($this, $messagePath);
    }
}
