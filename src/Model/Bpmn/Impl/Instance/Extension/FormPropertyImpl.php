<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    FormPropertyInterface,
    ValueInterface
};

class FormPropertyImpl extends BpmnModelElementInstanceImpl implements FormPropertyInterface
{
    protected static $idAttribute;
    protected static $nameAttribute;
    protected static $typeAttribute;
    protected static $requiredAttribute;
    protected static $readableAttribute;
    protected static $writeableAttribute;
    protected static $variableAttribute;
    protected static $expressionAttribute;
    protected static $datePatternAttribute;
    protected static $defaultAttribute;
    protected static $valueCollection;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FormPropertyInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_FORM_PROPERTY
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new FormPropertyImpl($instanceContext);
                }
            }
        );

        self::$idAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ID)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_NAME)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$typeAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_TYPE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$requiredAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_REQUIRED)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(false)
        ->build();

        self::$readableAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_READABLE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(true)
        ->build();

        self::$writeableAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_WRITEABLE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(true)
        ->build();

        self::$variableAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_VARIABLE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $expressionAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_EXPRESSION)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $datePatternAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_DATE_PATTERN)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $defaultAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_DEFAULT)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        $valueCollection = $sequenceBuilder->elementCollection(ValueInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getId(): string
    {
        return self::$idAttribute->getValue($this);
    }

    public function setId(string $id): void
    {
        self::$idAttribute->setValue($this, $id);
    }

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getType(): string
    {
        return self::$typeAttribute->getValue($this);
    }

    public function setType(string $type): void
    {
        self::$typeAttribute->setValue($this, $type);
    }

    public function isRequired(): bool
    {
        return self::$requiredAttribute->getValue($this);
    }

    public function setRequired(bool $isRequired): void
    {
        self::$requiredAttribute->setValue($this, $isRequired);
    }

    public function isReadable(): bool
    {
        return self::$readableAttribute->getValue($this);
    }

    public function setReadable(bool $isReadable): void
    {
        self::$readableAttribute->setValue($this, $isReadable);
    }

    public function isWriteable(): bool
    {
        return self::$writeableAttribute->getValue($this);
    }

    public function setWriteable(bool $isWriteable): void
    {
        self::$writeableAttribute->setValue($this, $isWriteable);
    }

    public function getVariable(): string
    {
        return self::$variableAttribute->getValue($this);
    }

    public function setVariable(string $variable): void
    {
        self::$variableAttribute->setValue($this, $variable);
    }

    public function getExpression(): string
    {
        return self::$expressionAttribute->getValue($this);
    }

    public function setExpression(string $expression): void
    {
        self::$expressionAttribute->setValue($this, $expression);
    }

    public function getDatePattern(): string
    {
        return self::$datePatternAttribute->getValue($this);
    }

    public function setDatePattern(string $datePattern): void
    {
        self::$datePatternAttribute->setValue($this, $datePattern);
    }

    public function getDefault(): string
    {
        return self::$defaultAttribute->getValue($this);
    }

    public function setDefault(string $default): void
    {
        self::$defaultAttribute->setValue($this, $default);
    }

    public function getValues(): array
    {
        return self::$valueCollection->get($this);
    }
}
