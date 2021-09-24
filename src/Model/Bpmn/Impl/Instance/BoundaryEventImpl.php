<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Builder\BoundaryEventBuilder;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivityInterface,
    BoundaryEventInterface,
    CatchEventInterface
};

class BoundaryEventImpl extends CatchEventImpl implements BoundaryEventInterface
{
    protected static $cancelActivityAttribute;
    protected static $attachedToRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            BoundaryEventInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_BOUNDARY_EVENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(CatchEventInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new BoundaryEventImpl($instanceContext);
                }
            }
        );

        self::$cancelActivityAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_CANCEL_ACTIVITY
        )
        ->defaultValue(true)
        ->build();

        self::$attachedToRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_ATTACHED_TO_REF
        )
        ->required()
        ->qNameAttributeReference(ActivityInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): BoundaryEventBuilder
    {
        return new BoundaryEventBuilder($this->modelInstance, $this);
    }

    public function cancelActivity(): bool
    {
        return self::$cancelActivityAttribute->getValue($this);
    }

    public function setCancelActivity(bool $cancelActivity): void
    {
        self::$cancelActivityAttribute->setValue($this, $cancelActivity);
    }

    public function getAttachedTo(): ActivityInterface
    {
        return self::$attachedToRefAttribute->getReferenceTargetElement($this);
    }

    public function setAttachedTo(ActivityInterface $attachedTo): void
    {
        self::$attachedToRefAttribute->setReferenceTargetElement($this, $attachedTo);
    }
}
