<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    DataInputInterface,
    DataOutputInterface,
    IoBindingInterface,
    OperationInterface
};

class IoBindingImpl extends BaseElementImpl implements IoBindingInterface
{
    protected static $operationRefAttribute;
    protected static $inputDataRefAttribute;
    protected static $outputDataRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            IoBindingInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_IO_BINDING
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new IoBindingImpl($instanceContext);
                }
            }
        );

        self::$operationRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_OPERATION_REF)
        ->required()
        ->qNameAttributeReference(OperationInterface::class)
        ->build();

        self::$inputDataRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_INPUT_DATA_REF)
        ->required()
        ->idAttributeReference(DataInputInterface::class)
        ->build();

        self::$outputDataRefAttribute = $typeBuilder->stringAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_OUTPUT_DATA_REF
        )
        ->required()
        ->idAttributeReference(DataOutputInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getOperation(): OperationInterface
    {
        return self::$operationRefAttribute->getReferenceTargetElement($this);
    }

    public function setOperation(OperationInterface $operation): void
    {
        self::$operationRefAttribute->setReferenceTargetElement($this, $operation);
    }

    public function getInputData(): DataInputInterface
    {
        return self::$inputDataRefAttribute->getReferenceTargetElement($this);
    }

    public function setInputData(DataInputInterface $inputData): void
    {
        self::$inputDataRefAttribute->setReferenceTargetElement($this, $inputData);
    }

    public function getOutputData(): DataOutputInterface
    {
        return self::$outputDataRefAttribute->getReferenceTargetElement($this);
    }

    public function setOutputData(DataOutputInterface $dataOutput): void
    {
        self::$outputDataRefAttribute->setReferenceTargetElement($this, $dataOutput);
    }
}
