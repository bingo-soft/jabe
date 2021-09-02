<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    DataInputInterface,
    DataOutputInterface,
    InputSetInterface,
    IoSpecificationInterface
};

class IoSpecificationImpl extends BaseElementImpl implements IoSpecificationInterface
{
    protected static $dataInputCollection;
    protected static $dataOutputCollection;
    protected static $inputSetCollection;
    protected static $outputSetCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            IoSpecificationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_IO_SPECIFICATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new IoSpecificationImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$dataInputCollection = $sequenceBuilder->elementCollection(DataInputInterface::class)
        ->build();

        self::$dataOutputCollection = $sequenceBuilder->elementCollection(DataOutputInterface::class)
        ->build();

        self::$inputSetCollection = $sequenceBuilder->elementCollection(InputSetInterface::class)
        ->required()
        ->build();

        self::$outputSetCollection = $sequenceBuilder->elementCollection(OutputSetInterface::class)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getDataInputs(): array
    {
        return self::$dataInputCollection->get($this);
    }

    public function getDataOutputs(): array
    {
        return self::$dataOutputCollection->get($this);
    }

    public function getInputSets(): array
    {
        return self::$inputSetCollection->get($this);
    }

    public function getOutputSets(): array
    {
        return self::$outputSetCollection->get($this);
    }
}
