<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ArtifactInterface,
    TextAnnotationInterface,
    TextInterface
};

class TextAnnotationImpl extends ArtifactImpl implements TextAnnotationInterface
{
    protected static $textFormatAttribute;
    protected static $textChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            TextAnnotationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_TEXT_ANNOTATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ArtifactInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new TextAnnotationImpl($instanceContext);
                }
            }
        );

        self::$textFormatAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_TEXT_FORMAT)
        ->defaultValue("text/plain")
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$textChild = $sequenceBuilder->element(TextInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getTextFormat(): string
    {
        return self::$textFormatAttribute->getValue($this);
    }

    public function setTextFormat(string $textFormat): void
    {
        self::$textFormatAttribute->setValue($this, $textFormat);
    }

    public function getText(): TextInterface
    {
        return self::$textChild->getChild($this);
    }

    public function setText(TextInterface $text): void
    {
        self::$textChild->setChild($this, $text);
    }
}
