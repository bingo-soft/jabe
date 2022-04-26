<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\ResourceAssignmentExpressionInterface;
use Jabe\Model\Bpmn\Instance\Extension\PotentialStarterInterface;

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
