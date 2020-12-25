<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    FormalExpressionInterface,
    ItemDefinitionInterface
};

class FormalExpressionImpl extends ExpressionImpl implements FormalExpression
{
    protected static $languageAttribute;
    protected static $evaluatesToTypeRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FormalExpressionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_FORMAL_EXPRESSION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ExpressionInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new FormalExpressionImpl($instanceContext);
                }
            }
        );

        self::$languageAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_LANGUAGE)
        ->build();

        self::$evaluatesToTypeRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_EVALUATES_TO_TYPE_REF
        )
        ->qNameAttributeReference(ItemDefinitionInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getLanguage(): string
    {
        return self::$languageAttribute->getValue($this);
    }

    public function setLanguage(string $language): void
    {
        self::$languageAttribute->setValue($this, $language);
    }

    public function getEvaluatesToType(): ItemDefinitionInterface
    {
        return self::$evaluatesToTypeRefAttribute->getReferenceTargetElement($this);
    }

    public function setEvaluatesToType(ItemDefinitionInterface $evaluatesToType): void
    {
        self::$evaluatesToTypeRefAttribute->setReferenceTargetElement($this, $evaluatesToType);
    }
}
