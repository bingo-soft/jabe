<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\ResourceAssignmentExpressionInterface;
use BpmPlatform\Model\Bpmn\Instance\Extension\PotentialStarterInterface;

class PotentialStarterImpl extends BpmnModelElementInstanceImpl implements PotentialStarterInterface
{
    protected static $resourceAssignmentExpressionChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            PotentialStarterInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_POTENTIAL_STARTER
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new PotentialStarterImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$resourceAssignmentExpressionChild = $sequenceBuilder->element(
            ResourceAssignmentExpressionInterface::class
        )
        ->build();

        $typeBuilder->build();
    }

    public function getResourceAssignmentExpression(): ResourceAssignmentExpressionInterface
    {
        return self::$resourceAssignmentExpressionChild->getChild($this);
    }

    public function setResourceAssignmentExpression(
        ResourceAssignmentExpressionInterface $resourceAssignmentExpression
    ): void {
        self::$resourceAssignmentExpressionChild->setChild($this, $resourceAssignmentExpression);
    }
}
