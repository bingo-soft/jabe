<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Di;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Di\{
    DiagramElementInterface,
    EdgeInterface,
    WaypointInterface
};

abstract class EdgeImpl extends DiagramElementImpl implements EdgeInterface
{
    protected static $waypointCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            EdgeInterface::class,
            BpmnModelConstants::DI_ELEMENT_EDGE
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->extendsType(DiagramElementInterface::class)
        ->abstractType();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$waypointCollection = $sequenceBuilder->elementCollection(WaypointInterface::class)
        ->minOccurs(2)
        ->build();

        $typeBuilder->build();
    }

    public function getWaypoints(): array
    {
        return self::$waypointCollection->get($this);
    }

    public function addWaypoint(WaypointInterface $point): void
    {
        self::$waypointCollection->add($this, $point);
    }
}
