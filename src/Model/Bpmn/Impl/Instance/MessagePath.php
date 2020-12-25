<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\FormalExpressionInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;

class MessagePath extends FormalExpressionImpl
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            MessagePath::class,
            BpmnModelConstants::BPMN_ELEMENT_MESSAGE_PATH
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(FormalExpressionInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new MessagePath($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}
