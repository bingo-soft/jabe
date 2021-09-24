<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\Builder\AbstractTaskBuilder;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivityInterface,
    TaskInterface
};
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

class TaskImpl extends ActivityImpl implements TaskInterface
{
    protected static $asyncAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            TaskInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_TASK
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ActivityInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new TaskImpl($instanceContext);
                }
            }
        );

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(false)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): AbstractTaskBuilder
    {
        throw new BpmnModelException("No builder implemented");
    }

    public function getDiagramElement(): BpmnShapeInterface
    {
        return parent::getDiagramElement();
    }

    public function isAsync(): bool
    {
        return self::$asyncAttribute->getValue($this);
    }

    public function setAsync(bool $isAsync): void
    {
        self::$asyncAttribute->setValue($this, $isAsync);
    }
}
