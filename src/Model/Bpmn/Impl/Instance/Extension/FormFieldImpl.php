<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    FormFieldInterface,
    PropertiesInterface,
    ValidationInterface,
    ValueInterface
};

class FormFieldImpl extends BpmnModelElementInstanceImpl implements FormFieldInterface
{
    protected static $idAttribute;
    protected static $labelAttribute;
    protected static $typeAttribute;
    protected static $datePatternAttribute;
    protected static $defaultValueAttribute;
    protected static $propertiesChild;
    protected static $validationChild;
    protected static $valueCollection;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FormFieldInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_FORM_FIELD
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new FormFieldImpl($instanceContext);
                }
            }
        );

        self::$idAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ID)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$labelAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_LABEL)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$typeAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_TYPE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$datePatternAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_DATE_PATTERN
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$defaultValueAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_DEFAULT_VALUE
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$propertiesChild = $sequenceBuilder->element(PropertiesInterface::class)
        ->build();

        self::$validationChild = $sequenceBuilder->element(ValidationInterface::class)
        ->build();

        self::$valueCollection = $sequenceBuilder->elementCollection(ValueInterface::class)
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

    public function getLabel(): string
    {
        return self::$labelAttribute->getValue($this);
    }

    public function setLabel(string $label): void
    {
        self::$labelAttribute->setValue($this, $label);
    }

    public function getType(): string
    {
        return self::$typeAttribute->getValue($this);
    }

    public function setType(string $type): void
    {
        self::$typeAttribute->setValue($this, $type);
    }

    public function getDatePattern(): string
    {
        return self::$datePatternAttribute->getValue($this);
    }

    public function setDatePattern(string $datePattern): void
    {
        self::$datePatternAttribute->setValue($this, $datePattern);
    }

    public function getDefaultValue(): string
    {
        return self::$defaultValueAttribute->getValue($this);
    }

    public function setDefaultValue(string $defaultValue): void
    {
        self::$defaultValueAttribute->setValue($this, $defaultValue);
    }

    public function getProperties(): PropertiesInterface
    {
        return self::$propertiesChild->getChild($this);
    }

    public function setProperties(PropertiesInterface $properties): void
    {
        self::$propertiesChild->setChild($this, $properties);
    }

    public function getValidation(): ValidationInterface
    {
        return self::$validationChild->getChild($this);
    }

    public function setValidation(ValidationInterface $validation): void
    {
        self::$validationChild->setChild($this, $validation);
    }

    public function getValues(): array
    {
        return self::$valueCollection->get($this);
    }
}
