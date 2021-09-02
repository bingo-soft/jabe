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

class DefinitionsImpl extends BpmnModelElementInstanceImpl implements DefinitionsInterface
{
    protected static $idAttribute;
    protected static $nameAttribute;
    protected static $targetNamespaceAttribute;
    protected static $expressionLanguageAttribute;
    protected static $typeLanguageAttribute;
    protected static $exporterAttribute;
    protected static $exporterVersionAttribute;
    protected static $importCollection;
    protected static $extensionCollection;
    protected static $rootElementCollection;
    protected static $bpmnDiagramCollection;
    protected static $relationshipCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DefinitionsInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_DEFINITIONS
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new DefinitionsImpl($instanceContext);
                }
            }
        );

        self::$idAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_ID)
        ->idAttribute()
        ->build();

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$targetNamespaceAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_TARGET_NAMESPACE
        )
        ->required()
        ->build();

        self::$expressionLanguageAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_EXPRESSION_LANGUAGE
        )
        ->defaultValue(BpmnModelConstants::XPATH_NS)
        ->build();

        self::$typeLanguageAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_TYPE_LANGUAGE)
        ->defaultValue(BpmnModelConstants::XML_SCHEMA_NS)
        ->build();

        self::$exporterAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_EXPORTER)
        ->build();

        self::$exporterVersionAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_EXPORTER_VERSION
        )
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$importCollection = $sequenceBuilder->elementCollection(ImportInterface::class)
        ->build();

        self::$extensionCollection = $sequenceBuilder->elementCollection(ExtensionInterface::class)
        ->build();

        self::$rootElementCollection = $sequenceBuilder->elementCollection(RootElementInterface::class)
        ->build();

        self::$bpmnDiagramCollection = $sequenceBuilder->elementCollection(BpmnDiagramInterface::class)
        ->build();

        self::$relationshipCollection = $sequenceBuilder->elementCollection(RelationshipInterface::class)
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

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getTargetNamespace(): string
    {
        return self::$targetNamespaceAttribute->getValue($this);
    }

    public function setTargetNamespace(string $namespace): void
    {
        self::$targetNamespaceAttribute->setValue($this, $namespace);
    }

    public function getExpressionLanguage(): string
    {
        return self::$expressionLanguageAttribute->getValue($this);
    }

    public function setExpressionLanguage(string $expressionLanguage): void
    {
        self::$expressionLanguageAttribute->setValue($this, $expressionLanguage);
    }

    public function getTypeLanguage(): string
    {
        return self::$typeLanguageAttribute->getValue($this);
    }

    public function setTypeLanguage(string $typeLanguage): void
    {
        self::$typeLanguageAttribute->setValue($this, $typeLanguage);
    }

    public function getExporter(): string
    {
        return self::$exporterAttribute->getValue($this);
    }

    public function setExporter(string $exporter): void
    {
        self::$exporterAttribute->setValue($this, $exporter);
    }

    public function getExporterVersion(): string
    {
        return self::$exporterVersionAttribute->getValue($this);
    }

    public function setExporterVersion(string $exporterVersion): void
    {
        self::$exporterVersionAttribute->setValue($this, $exporterVersion);
    }

    public function getImports(): array
    {
        return self::$importCollection->get($this);
    }

    public function getExtensions(): array
    {
        return self::$extensionCollection->get($this);
    }

    public function getRootElements(): array
    {
        return self::$rootElementCollection->get($this);
    }

    public function getBpmDiagrams(): array
    {
        return self::$bpmnDiagramCollection->get($this);
    }

    public function getRelationships(): array
    {
        return self::$relationshipCollection->get($this);
    }
}
