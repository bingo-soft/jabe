<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\OutInterface;

class OutImpl extends BpmnModelElementInstanceImpl implements OutInterface
{
    protected static $sourceAttribute;
    protected static $sourceExpressionAttribute;
    protected static $variablesAttribute;
    protected static $targetAttribute;
    protected static $localAttribute;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            OutInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_OUT
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new OutImpl($instanceContext);
                }
            }
        );

        self::$sourceAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_SOURCE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$sourceExpressionAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_SOURCE_EXPRESSION
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$variablesAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_VARIABLES)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$targetAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_TARGET)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$localAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_LOCAL)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $typeBuilder->build();
    }

    public function getSource(): string
    {
        return self::$sourceAttribute->getValue($this);
    }

    public function setSource(string $source): void
    {
        self::$sourceAttribute->setValue($this, $source);
    }

    public function getSourceExpression(): string
    {
        return self::$sourceExpressionAttribute->getValue($this);
    }

    public function setSourceExpression(string $sourceExpression): void
    {
        self::$sourceExpressionAttribute->setValue($this, $sourceExpression);
    }

    public function getVariables(): string
    {
        return self::$variablesAttribute->getValue($this);
    }

    public function setVariables(string $variables): void
    {
        self::$variablesAttribute->setValue($this, $variables);
    }

    public function getTarget(): string
    {
        return self::$targetAttribute->getValue($this);
    }

    public function setTarget(string $target): void
    {
        self::$targetAttribute->setValue($this, $target);
    }

    public function getLocal(): bool
    {
        return self::$localAttribute->getValue($this);
    }

    public function setLocal(bool $local): void
    {
        self::$localAttribute->setValue($this, $local);
    }
}
