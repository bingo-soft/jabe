<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    AuditingInterface,
    BaseElementInterface,
    CategoryValueInterface,
    FlowElementInterface,
    MonitoringInterface
};

abstract class FlowElementImpl extends BaseElementImpl implements FlowElementInterface
{
    protected static $nameAttribute;
    protected static $auditingChild;
    protected static $monitoringChild;
    protected static $categoryValueRefCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FlowElementInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_FLOW_ELEMENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->abstractType();

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
                               ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$auditingChild = $sequenceBuilder->element(AuditingInterface::class)
                               ->build();

        self::$monitoringChild = $sequenceBuilder->element(MonitoringInterface::class)
                                 ->build();

        self::$categoryValueRefCollection = $sequenceBuilder->elementCollection(CategoryValueRefInterface::class)
        ->qNameElementReferenceCollection(CategoryValueInterface::class)
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

    public function getAuditing(): AuditingInterface
    {
        return self::$auditingChild->getChild($this);
    }

    public function setAuditing(AuditingInterface $auditing): void
    {
        self::$auditingChild->setChild($this, $auditing);
    }

    public function getMonitoring(): MonitoringInterface
    {
        self::$monitoringChild->getChild($this);
    }

    public function setMonitoring(MonitoringInterface $monitoring): void
    {
        self::$monitoringChild->setChild($this, $monitoring);
    }

    public function getCategoryValueRefs(): array
    {
        return self::$categoryValueRefCollection->getReferenceTargetElements($this);
    }
}
