<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    DocumentationInterface,
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
            new class implements ModelTypeInstanceProviderInterface
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
