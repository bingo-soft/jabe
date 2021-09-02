<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Builder\StartEventBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    StartEventInterface,
    CatchEventInterface
};

class StartEventImpl extends CatchEventImpl implements StartEventInterface
{
    protected static $isInterruptingAttribute;
    protected static $asyncAttribute;
    protected static $formHandlerClassAttribute;
    protected static $formKeyAttribute;
    protected static $initiatorAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            StartEventInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_START_EVENT
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(CatchEventInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new StartEventImpl($instanceContext);
                }
            }
        );

        self::$isInterruptingAttribute = $typeBuilder->booleanAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_IS_INTERRUPTING
        )
        ->defaultValue(true)
        ->build();

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::NS)
        ->defaultValue(false)
        ->build();

        self::$formHandlerClassAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::ATTRIBUTE_FORM_HANDLER_CLASS
        )
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$formKeyAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_FORM_KEY)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        self::$initiatorAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::ATTRIBUTE_INITIATOR)
        ->namespace(BpmnModelConstants::NS)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): StartEventBuilder
    {
        return new StartEventBuilder($this->modelInstance, $this);
    }

    public function isInterrupting(): bool
    {
        return self::$isInterruptingAttribute->getValue($this);
    }

    public function setInterrupting(bool $isInterrupting): void
    {
        self::$isInterruptingAttribute->setValue($this, $isInterrupting);
    }

    public function getFormHandlerClass(): string
    {
        return self::$formHandlerClassAttribute->getValue($this);
    }

    public function setFormHandlerClass(string $formHandlerClass): void
    {
        self::$formHandlerClassAttribute->setValue($this, $formHandlerClass);
    }

    public function getFormKey(): string
    {
        return self::$formKeyAttribute->getValue($this);
    }

    public function setFormKey(string $formKey): void
    {
        self::$formKeyAttribute->setValue($this, $formKey);
    }

    public function getInitiator(): string
    {
        return self::$initiatorAttribute->getValue($this);
    }

    public function setInitiator(string $initiator): void
    {
        self::$initiatorAttribute->setValue($this, $initiator);
    }
}
