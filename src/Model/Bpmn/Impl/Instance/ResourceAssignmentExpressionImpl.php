<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    ExpressionInterface,
    ResourceAssignmentExpressionInterface
};

class ResourceAssignmentExpressionImpl extends BaseElementImpl implements ResourceAssignmentExpressionInterface
{
    protected static $expressionChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ResourceAssignmentExpressionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_RESOURCE_ASSIGNMENT_EXPRESSION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ResourceAssignmentExpressionImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$expressionChild = $sequenceBuilder->element(ExpressionInterface::class)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getExpression(): ExpressionInterface
    {
        return self::$expressionChild->getChild($this);
    }

    public function setExpression(ExpressionInterface $expression): void
    {
        self::$expressionChild->setChild($this, $expression);
    }
}
