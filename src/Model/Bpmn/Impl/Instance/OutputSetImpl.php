<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    DataOutputInterface,
    OutputSetInterface,
    InputSetInterface
};

class OutputSetImpl extends BaseElementImpl implements OutputSetInterface
{
    protected static $nameAttribute;
    protected static $dataOutputDataOutputRefsCollection;
    protected static $optionalOutputRefsCollection;
    protected static $whileExecutingOutputRefsCollection;
    protected static $inputSetInputSetRefsCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            OutputSetInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_OUTPUT_SET
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new OutputSetImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute("name")
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$dataOutputDataOutputRefsCollection = $sequenceBuilder->elementCollection(DataOutputRefs::class)
        ->idElementReferenceCollection(DataOutputInterface::class)
        ->build();

        self::$optionalOutputRefsCollection = $sequenceBuilder->elementCollection(OptionalOutputRefs::class)
        ->idElementReferenceCollection(DataOutputInterface::class)
        ->build();

        self::$whileExecutingOutputRefsCollection = $sequenceBuilder->elementCollection(WhileExecutingOutputRefs::class)
        ->idElementReferenceCollection(DataOutputInterface::class)
        ->build();

        self::$inputSetInputSetRefsCollection = $sequenceBuilder->elementCollection(InputSetRefs::class)
        ->idElementReferenceCollection(InputSetInterface::class)
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

    public function getDataOutputRefs(): array
    {
        return self::$dataOutputDataOutputRefsCollection->getReferenceTargetElements($this);
    }

    public function getOptionalOutputRefs(): array
    {
        return self::$optionalOutputRefsCollection->getReferenceTargetElements($this);
    }

    public function getWhileExecutingOutputRefs(): array
    {
        return self::$whileExecutingOutputRefsCollection->getReferenceTargetElements($this);
    }

    public function getInputSetRefs(): array
    {
        return self::$inputSetInputSetRefsCollection->getReferenceTargetElements($this);
    }
}
