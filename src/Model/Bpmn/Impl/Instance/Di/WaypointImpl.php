<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Di;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\Dc\PointImpl;
use Jabe\Model\Bpmn\Instance\Dc\PointInterface;
use Jabe\Model\Bpmn\Instance\Di\WaypointInterface;

class WaypointImpl extends PointImpl implements WaypointInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            WaypointInterface::class,
            BpmnModelConstants::DI_ELEMENT_WAYPOINT
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->extendsType(PointInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new WaypointImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}
