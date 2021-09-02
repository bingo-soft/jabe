<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ConversationNodeInterface,
    SubConversationInterface
};

class SubConversationImpl extends ConversationNodeImpl implements SubConversationInterface
{
    protected static $conversationNodeCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            SubConversationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_SUB_CONVERSATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ConversationNodeInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new SubConversationImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$conversationNodeCollection = $sequenceBuilder->elementCollection(ConversationNodeInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getConversationNodes(): array
    {
        return self::$conversationNodeCollection->get($this);
    }
}
