<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Di;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Di\{
    DiagramElementInterface,
    ExtensionInterface
};

abstract class DiagramElementImpl extends BpmnModelElementInstanceImpl implements DiagramElementInterface
{
    protected static $idAttribute;
    protected static $extensionChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DiagramElementInterface::class,
            BpmnModelConstants::DI_ELEMENT_DIAGRAM_ELEMENT
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->abstractType();

        self::$idAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::DI_ATTRIBUTE_ID)
        ->idAttribute()
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$extensionChild = $sequenceBuilder->element(ExtensionInterface::class)
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

    public function getExtension(): ExtensionInterface
    {
        return self::$extensionChild->getChild($this);
    }

    public function setExtension(ExtensionInterface $extension): void
    {
        self::$extensionChild->setChild($this, $extension);
    }
}
