<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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
