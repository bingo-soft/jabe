<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\{
    ExpressionInterface,
    FieldInterface,
    StringInterface
};

class FieldImpl extends BpmnModelElementInstanceImpl implements FieldInterface
{
    protected static $nameAttribute;
    protected static $expressionAttribute;
    protected static $stringValueAttribute;
    protected static $expressionChild;
    protected static $stringChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FieldInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_FIELD
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new FieldImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_NAME)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$expressionAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_EXPRESSION)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$stringValueAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_STRING_VALUE
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$expressionChild = $sequenceBuilder->element(ExpressionInterface::class)
        ->build();

        self::$stringChild = $sequenceBuilder->element(StringInterface::class)
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

    public function getExpression(): string
    {
        return self::$expressionAttribute->getValue($this);
    }

    public function setExpression(string $expression): void
    {
        self::$expressionAttribute->setValue($this, $expression);
    }

    public function getStringValue(): string
    {
        return self::$stringValueAttribute->getValue($this);
    }

    public function setStringValue(string $stringValue): void
    {
        self::$stringValueAttribute->setValue($this, $stringValue);
    }

    public function getString(): StringInterface
    {
        return self::$stringChild->getChild($this);
    }

    public function setString(StringInterface $string): void
    {
        self::$stringChild->setChild($this, $string);
    }

    public function getExpressionChild(): ExpressionInterface
    {
        return self::$expressionChild->getChild($this);
    }

    public function setExpressionChild(ExpressionInterface $expression): void
    {
        self::$expressionChild->setChild($this, $expression);
    }
}
