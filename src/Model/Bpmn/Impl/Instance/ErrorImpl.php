<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ErrorInterface,
    ItemDefinitionInterface,
    RootElementInterface
};

class ErrorImpl extends RootElementImpl implements ErrorInterface
{
    protected static $nameAttribute;
    protected static $errorCodeAttribute;
    protected static $errorMessageAttribute;
    protected static $structureRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ErrorInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_ERROR
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ErrorImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$errorCodeAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_ERROR_CODE)
        ->build();

        self::$errorMessageAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ERROR_MESSAGE)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$structureRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_STRUCTURE_REF)
        ->qNameAttributeReference(ItemDefinitionInterface::class)
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

    public function getErrorCode(): string
    {
        return self::$errorCodeAttribute->getValue($this);
    }

    public function setErrorCode(string $errorCode): void
    {
        self::$errorCodeAttribute->setValue($this, $errorCode);
    }

    public function getErrorMessage(): string
    {
        return self::$errorMessageAttribute->getValue($this);
    }

    public function setErrorMessage(string $errorMessage): void
    {
        self::$errorMessageAttribute->setValue($this, $errorMessage);
    }

    public function getStructure(): ItemDefinitionInterface
    {
        return self::$structureRefAttribute->getReferenceTargetElement($this);
    }

    public function setStructure(ItemDefinitionInterface $structure): void
    {
        self::$structureRefAttribute->setReferenceTargetElement($this, $structure);
    }
}
