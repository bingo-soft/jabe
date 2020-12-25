<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ExtensionInterface,
    ImportInterface,
    RelationshipInterface,
    RootElementInterface
};

class DocumentationImpl extends BpmnModelElementInstanceImpl implements DocumentationInterface
{
    protected static $idAttribute;
    protected static $textFormatAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DocumentationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DOCUMENTATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DocumentationImpl($instanceContext);
                }
            }
        );

        self::$idAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_ID)
        ->idAttribute()
        ->build();

        self::$textFormatAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_TEXT_FORMAT)
        ->defaultValue("text/plain")
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

    public function getTextFormat(): string
    {
        return self::$textFormatAttribute->getValue($this);
    }

    public function setTextFormat(string $textFormat): void
    {
        self::$textFormatAttribute->setValue($this, $textFormat);
    }
}
