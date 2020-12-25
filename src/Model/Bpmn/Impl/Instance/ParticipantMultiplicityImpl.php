<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    ParticipantMultiplicityInterface
};

class ParticipantMultiplicityImpl extends BaseElementImpl implements ParticipantMultiplicityInterface
{
    protected static $minimumAttribute;
    protected static $maximumAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ParticipantMultiplicityInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_PARTICIPANT_MULTIPLICITY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ParticipantMultiplicityImpl($instanceContext);
                }
            }
        );

        self::$minimumAttribute = $typeBuilder->integerAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_MINIMUM)
        ->defaultValue(0)
        ->build();

        self::$maximumAttribute = $typeBuilder->integerAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_MAXIMUM)
        ->defaultValue(1)
        ->build();

        $typeBuilder->build();
    }

    public function getMinimum(): int
    {
        return self::$minimumAttribute->getValue($this);
    }

    public function setMinimum(int $minimum): void
    {
        self::$minimumAttribute->setValue($this, $minimum);
    }

    public function getMaximum(): int
    {
        return self::$maximumAttribute->getValue(this);
    }

    public function setMaximum(int $maximum): void
    {
        self::$maximumAttribute->setValue($this, $maximum);
    }
}
