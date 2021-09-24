<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Bpmn\Builder\CallActivityBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivityInterface,
    CallActivityInterface
};

class CallActivityImpl extends ActivityImpl implements CallActivityInterface
{
    protected static $calledElementAttribute;
    protected static $asyncAttribute;
    protected static $calledElementBindingAttribute;
    protected static $calledElementVersionAttribute;
    protected static $calledElementVersionTagAttribute;
    protected static $calledElementTenantIdAttribute;
    protected static $caseRefAttribute;
    protected static $caseBindingAttribute;
    protected static $caseVersionAttribute;
    protected static $caseTenantIdAttribute;
    protected static $variableMappingClassAttribute;
    protected static $variableMappingDelegateExpressionAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CallActivityInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CALL_ACTIVITY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ActivityInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CallActivityImpl($instanceContext);
                }
            }
        );

        self::$calledElementAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_CALLED_ELEMENT)
        ->build();

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(false)
        ->build();

        self::$calledElementBindingAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_CALLED_ELEMENT_BINDING
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$calledElementVersionAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_CALLED_ELEMENT_VERSION
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$calledElementVersionTagAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_CALLED_ELEMENT_VERSION_TAG
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$caseRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_CASE_REF)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$caseBindingAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_CASE_BINDING)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$caseVersionAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_CASE_VERSION)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$calledElementTenantIdAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_CALLED_ELEMENT_TENANT_ID
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$caseTenantIdAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_CASE_TENANT_ID)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$variableMappingClassAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_VARIABLE_MAPPING_CLASS
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$variableMappingDelegateExpressionAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_VARIABLE_MAPPING_DELEGATE_EXPRESSION
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();


        $typeBuilder->build();
    }

    public function builder(): CallActivityBuilder
    {
        return new CallActivityBuilder($this->modelInstance, $this);
    }

    public function isAsync(): bool
    {
        return self::$asyncAttribute->getValue($this);
    }

    public function setAsync(bool $isAsync): void
    {
        self::$asyncAttribute->setValue($this, $isAsync);
    }

    public function getCalledElement(): string
    {
        return self::$calledElementAttribute->getValue($this);
    }

    public function setCalledElement(string $calledElement): void
    {
        self::$calledElementAttribute->setValue($this, $calledElement);
    }

    public function getCalledElementBinding(): string
    {
        return self::$calledElementBindingAttribute->getValue($this);
    }

    public function setCalledElementBinding(string $calledElementBinding): void
    {
        self::$calledElementBindingAttribute->setValue($this, $calledElementBinding);
    }

    public function getCalledElementVersion(): string
    {
        return self::$calledElementVersionAttribute->getValue($this);
    }

    public function setCalledElementVersion(string $calledElementVersion): void
    {
        self::$calledElementVersionAttribute->setValue($this, $calledElementVersion);
    }

    public function getCalledElementVersionTag(): string
    {
        return self::$calledElementVersionTagAttribute->getValue($this);
    }

    public function setCalledElementVersionTag(string $calledElementVersionTag): void
    {
        self::$calledElementVersionTagAttribute->setValue($this, $calledElementVersionTag);
    }

    public function getCalledElementTenantId(): string
    {
        return self::$calledElementTenantIdAttribute->getValue($this);
    }

    public function setCalledElementTenantId(string $calledElementTenantId): void
    {
        self::$calledElementVTenantIdAttribute->setValue($this, $calledElementTenantId);
    }

    public function getCaseRef(): string
    {
        return self::$caseRefAttribute->getValue($this);
    }

    public function setCaseRef(string $caseRef): void
    {
        self::$caseRefAttribute->setValue($this, $caseRef);
    }

    public function getCaseBinding(): string
    {
        return self::$caseBindingAttribute->getValue($this);
    }

    public function setCaseBinding(string $caseBinding): void
    {
        self::$caseBindingAttribute->setValue($this, $caseBinding);
    }

    public function getCaseVersion(): string
    {
        return self::$caseVersionAttribute->getValue($this);
    }

    public function setCaseVersion(string $caseVersion): void
    {
        self::$caseVersionAttribute->setValue($this, $caseVersion);
    }

    public function getCaseTenantId(): string
    {
        return self::$caseTenantIdAttribute->getValue($this);
    }

    public function setCaseTenantId(string $caseTenantId): void
    {
        self::$caseTenantIdAttribute->setValue($this, $caseTenantId);
    }

    public function getVariableMappingClass(): string
    {
        return self::$variableMappingClassAttribute->getValue($this);
    }

    public function setVariableMappingClass(string $class): void
    {
        self::$variableMappingClassAttribute->setValue($this, $class);
    }

    public function getVariableMappingDelegateExpression(): string
    {
        return self::$variableMappingDelegateExpressionAttribute->getValue($this);
    }

    public function setVariableMappingDelegateExpression(string $expression): void
    {
        self::$variableMappingDelegateExpressionAttribute->setValue($this, $expression);
    }
}
