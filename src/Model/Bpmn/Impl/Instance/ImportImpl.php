<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ImportInterface
};

class ImportImpl extends BpmnModelElementInstanceImpl implements ImportInterface
{
    protected static $namespaceAttribute;
    protected static $locationAttribute;
    protected static $importTypeAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ImportInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_IMPORT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ImportImpl($instanceContext);
                }
            }
        );


        self::$namespaceAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAMESPACE)
        ->required()
        ->build();

        self::$locationAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_LOCATION)
        ->required()
        ->build();

        self::$importTypeAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_IMPORT_TYPE)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getNamespace(): string
    {
        return self::$namespaceAttribute->getValue($this);
    }

    public function setNamespace(string $namespace): void
    {
        self::$namespaceAttribute->setValue($this, $namespace);
    }

    public function getLocation(): string
    {
        return self::$locationAttribute->getValue($this);
    }

    public function setLocaltion(string $localtion): void
    {
        self::$locationAttribute->setValue($this, $localtion);
    }

    public function getImportType(): string
    {
        return self::$importTypeAttribute->getValue($this);
    }

    public function setImportType(string $importType): void
    {
        self::$importTypeAttribute->setValue($this, $importType);
    }
}
