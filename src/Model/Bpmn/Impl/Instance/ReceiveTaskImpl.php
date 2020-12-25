<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Builder\ReceiveTaskBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    MessageInterface,
    OperationInterface,
    ReceiveTaskInterface,
    TaskInterface
};

class ReceiveTaskImpl extends TaskImpl implements ReceiveTaskInterface
{
    protected static $implementationAttribute;
    protected static $instantiateAttribute;
    protected static $messageRefAttribute;
    protected static $operationRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ReceiveTaskInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_RECEIVE_TASK
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(TaskInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ReceiveTaskImpl($instanceContext);
                }
            }
        );

        self::$implementationAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_IMPLEMENTATION
        )
        ->defaultValue("##WebService")
        ->build();

        self::$instantiateAttribute = $typeBuilder->booleanAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_INSTANTIATE)
        ->defaultValue(false)
        ->build();

        self::$messageRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_MESSAGE_REF)
        ->qNameAttributeReference(MessageInterface::class)
        ->build();

        self::$operationRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_OPERATION_REF)
        ->qNameAttributeReference(OperationInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function builder(): ReceiveTaskBuilder
    {
        return new ReceiveTaskBuilder($this->modelInstance, $this);
    }

    public function getImplementation(): string
    {
        return self::$implementationAttribute->getValue($this);
    }

    public function setImplementation(string $implementation): void
    {
        self::$implementationAttribute->setValue($this, $implementation);
    }

    public function instantiate(): bool
    {
        return self::$instantiateAttribute->getValue($this);
    }

    public function setInstantiate(bool $instantiate): void
    {
        self::$instantiateAttribute->setValue($this, $instantiate);
    }

    public function getMessage(): MessageInterface
    {
        return self::$messageRefAttribute->getReferenceTargetElement($this);
    }

    public function setMessage(MessageInterface $message): void
    {
        self::$messageRefAttribute->setReferenceTargetElement($this, $message);
    }

    public function getOperation(): OperationInterface
    {
        return self::$operationRefAttribute->getReferenceTargetElement($this);
    }

    public function setOperation(OperationInterface $operation): void
    {
        self::$operationRefAttribute->setReferenceTargetElement($this, $operation);
    }
}
