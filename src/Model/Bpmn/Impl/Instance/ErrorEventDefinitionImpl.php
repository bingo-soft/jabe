<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Builder\ErrorEventDefinitionBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ErrorEventDefinitionInterface,
    ErrorInterface,
    EventDefinitionInterface
};

class ErrorEventDefinitionImpl extends EventDefinitionImpl implements ErrorEventDefinitionInterface
{
    protected static $errorRefAttribute;
    protected static $errorCodeVariableAttribute;
    protected static $errorMessageVariableAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ErrorEventDefinitionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_ERROR_EVENT_DEFINITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(EventDefinitionInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ErrorEventDefinitionImpl($instanceContext);
                }
            }
        );

        self::$errorRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_ERROR_REF)
        ->qNameAttributeReference(ErrorInterface::class)
        ->build();

        self::$errorCodeVariableAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::ATTRIBUTE_ERROR_CODE_VARIABLE
        )
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$errorMessageVariableAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::ATTRIBUTE_ERROR_MESSAGE_VARIABLE
        )
        ->namespace(BpmnModelConstants::NS)
        ->build();

        $typeBuilder->build();
    }

    public function getError(): ErrorInterface
    {
        return self::$errorRefAttribute->getReferenceTargetElement($this);
    }

    public function setError(ErrorInterface $error): void
    {
        self::$errorRefAttribute->setReferenceTargetElement($this, $error);
    }

    public function getErrorCodeVariable(): string
    {
        return self::$errorCodeVariableAttribute->getValue($this);
    }

    public function setErrorCodeVariable(string $errorCodeVariable): void
    {
        self::$errorCodeVariableAttribute->setValue($this, $errorCodeVariable);
    }

    public function getErrorMessageVariable(): string
    {
        return self::$errorMessageVariableAttribute->getValue($this);
    }

    public function setErrorMessageVariable(string $errorMessageVariable): void
    {
        self::$errorMessageVariableAttribute->setValue($this, $errorMessageVariable);
    }
}
