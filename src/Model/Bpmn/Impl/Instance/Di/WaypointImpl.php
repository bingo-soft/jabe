<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Di;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\Dc\PointImpl;
use BpmPlatform\Model\Bpmn\Instance\Dc\PointInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\WaypointInterface;

class WaypointImpl extends PointImpl implements WaypointInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ExtensionInterface::class,
            BpmnModelConstants::DI_ELEMENT_WAYPOINT
        )
        ->namespaceUri(BpmnModelConstants::DI_NS)
        ->extendsType(PointInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
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
