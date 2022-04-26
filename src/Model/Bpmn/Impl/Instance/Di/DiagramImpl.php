<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Di;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Di\DiagramInterface;

abstract class DiagramImpl extends BpmnModelElementInstanceImpl implements DiagramInterface
{
    protected static $nameAttribute;
    protected static $documentationAttribute;
    protected static $resolutionAttribute;
    protected static $idAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DiagramInterface::class,
            BpmnModelConstants::DI_ELEMENT_DIAGRAM
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->abstractType();

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::DI_ATTRIBUTE_NAME)
        ->build();

        self::$documentationAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::DI_ATTRIBUTE_DOCUMENTATION)
        ->build();

        self::$resolutionAttribute = $typeBuilder->doubleAttribute(BpmnModelConstants::DI_ATTRIBUTE_RESOLUTION)
        ->build();

        self::$idAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::DI_ATTRIBUTE_ID)
        ->idAttribute()
        ->build();

        $typeBuilder->build();
    }

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getDocumentation(): string
    {
        return self::$documentationAttribute->getValue($this);
    }

    public function setDocumentation(string $documentation): void
    {
        self::$documentationAttribute->setValue($this, $documentation);
    }

    public function getResolution(): float
    {
        return self::$resolutionAttribute->getValue($this);
    }

    public function setResolution(float $resolution): void
    {
        self::$resolutionAttribute->setValue($this, $resolution);
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
