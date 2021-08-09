<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Di;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Di\StyleInterface;

abstract class StyleImpl extends BpmnModelElementInstanceImpl implements StyleInterface
{
    protected static $idAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            StyleInterface::class,
            BpmnModelConstants::DI_ELEMENT_STYLE
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->abstractType();

        self::$idAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::DI_ATTRIBUTE_ID)
        ->idAttribute()
        ->build();

        $typeBuilder->build();
    }

    public function getId(): string
    {
        return self::$idAttribute->getValue($this);
    }

    public function setId(string $id): void
    {
        self::$idAttribute->setValue($this, $id);
    }
}
