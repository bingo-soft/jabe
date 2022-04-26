<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Bpmn\Builder\StartEventBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
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

        self::$asyncAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_ASYNC)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->defaultValue(false)
        ->build();

        self::$formHandlerClassAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::EXTENSION_ATTRIBUTE_FORM_HANDLER_CLASS
        )
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$formKeyAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_FORM_KEY)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$initiatorAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_INITIATOR)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
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

    public function isAsync(): bool
    {
        return self::$asyncAttribute->getValue($this);
    }

    public function setAsync(bool $isAsync): void
    {
        self::$asyncAttribute->setValue($this, $isAsync);
    }
}
