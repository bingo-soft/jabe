<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    DocumentationInterface,
    ExtensionInterface
};

class ExtensionImpl extends BpmnModelElementInstanceImpl implements ExtensionInterface
{
    protected static $definitionAttribute;
    protected static $mustUnderstandAttribute;
    protected static $documentationCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ExtensionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_EXTENSION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ExtensionImpl($instanceContext);
                }
            }
        );

        self::$definitionAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_DEFINITION)
        ->build();

        self::$mustUnderstandAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_MUST_UNDERSTAND
        )
        ->defaultValue(false)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$documentationCollection = $sequenceBuilder->elementCollection(DocumentationInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getDefinition(): string
    {
        return self::$definitionAttribute->getValue($this);
    }

    public function setDefinition(string $definition): void
    {
        self::$definitionAttribute->setValue($this, $definition);
    }

    public function mustUnderstand(): bool
    {
        return self::$mustUnderstandAttribute->getValue($this);
    }

    public function setMustUnderstand(bool $mustUnderstand): void
    {
        self::$mustUnderstandAttribute->setValue($this, $mustUnderstand);
    }

    public function getDocumentations(): array
    {
        return self::$documentationCollection->get($this);
    }
}
