<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ConditionExpressionInterface,
    FormalExpressionInterface
};

class ConditionExpressionImpl extends FormalExpressionImpl implements ConditionExpressionInterface
{
    protected static $typeAttribute;
    protected static $resourceAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ConditionExpressionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CONDITION_EXPRESSION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(FormalExpressionInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ConditionExpressionImpl($instanceContext);
                }
            }
        );

        self::$typeAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::XSI_ATTRIBUTE_TYPE)
        ->namespace(BpmnModelConstants::XSI_NS)
        ->defaultValue("tFormalExpression")
        ->build();

        self::$resourceAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_RESOURCE)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        $typeBuilder->build();
    }

    public function getType(): string
    {
        return self::$typeAttribute->getValue($this);
    }

    public function setType(string $type): void
    {
        self::$typeAttribute->setValue($this, $type);
    }

    public function getResource(): string
    {
        return self::$resourceAttribute->getValue($this);
    }

    public function setResource(string $resource): void
    {
        self::$resourceAttribute->setValue($this, $resource);
    }
}
