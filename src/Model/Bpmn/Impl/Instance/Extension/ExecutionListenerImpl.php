<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    ExecutionListenerInterface,
    FieldInterface,
    ScriptInterface
};

class ExecutionListenerImpl extends BpmnModelElementInstanceImpl implements ExecutionListenerInterface
{
    protected static $eventAttribute;
    protected static $classAttribute;
    protected static $expressionAttribute;
    protected static $delegateExpressionAttribute;
    protected static $fieldCollection;
    protected static $scriptChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ExecutionListenerInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_EXECUTION_LISTENER
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ExecutionListenerImpl($instanceContext);
                }
            }
        );

        self::$eventAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_EVENT)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$classAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_CLASS)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$expressionAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_EXPRESSION)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$delegateExpressionAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_DELEGATE_EXPRESSION
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$fieldCollection = $sequenceBuilder->elementCollection(FieldInterface::class)
        ->build();

        self::$scriptChild = $sequenceBuilder->element(ScriptInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getEvent(): string
    {
        return self::$eventAttribute->getValue($this);
    }

    public function setEvent(string $event): void
    {
        self::$eventAttribute->setValue($this, $event);
    }

    public function getClass(): string
    {
        return self::$classAttribute->getValue($this);
    }

    public function setClass(string $class): void
    {
        self::$classAttribute->setValue($this, $class);
    }

    public function getExpression(): string
    {
        return self::$expressionAttribute->getValue($this);
    }

    public function setExpression(string $expression): void
    {
        self::$expressionAttribute->setValue($this, $expression);
    }

    public function getDelegateExpression(): string
    {
        return self::$delegateExpressionAttribute->getValue($this);
    }

    public function setDelegateExpression(string $delegateExpression): void
    {
        self::$delegateExpressionAttribute->setValue($this, $delegateExpression);
    }

    public function getFields(): array
    {
        return self::$fieldCollection->get($this);
    }

    public function getScript(): ScriptInterface
    {
        return self::$scriptChild->getChild($this);
    }

    public function setScript(ScriptInterface $script): void
    {
        self::$scriptChild->setChild($this, $script);
    }
}
