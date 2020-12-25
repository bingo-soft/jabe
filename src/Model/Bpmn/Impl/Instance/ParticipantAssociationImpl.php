<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    BaseElementInterface,
    ParticipantAssociationInterface,
    ParticipantInterface
};

class ParticipantAssociationImpl extends BaseElementImpl implements ParticipantAssociationInterface
{
    protected static $innerParticipantRefChild;
    protected static $outerParticipantRefChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ParticipantAssociationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_PARTICIPANT_ASSOCIATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ParticipantAssociationImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$innerParticipantRefChild = $sequenceBuilder->element(InnerParticipantRef::class)
        ->required()
        ->qNameElementReference(ParticipantInterface::class)
        ->build();

        self::$outerParticipantRefChild = $sequenceBuilder->element(OuterParticipantRef::class)
        ->required()
        ->qNameElementReference(ParticipantInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getInnerParticipant(): ParticipantInterface
    {
        return self::$innerParticipantRefChild->getReferenceTargetElement($this);
    }

    public function setInnerParticipant(ParticipantInterface $innerParticipant): void
    {
        self::$innerParticipantRefChild->setReferenceTargetElement($this, $innerParticipant);
    }

    public function getOuterParticipant(): ParticipantInterface
    {
        return self::$outerParticipantRefChild->getReferenceTargetElement($this);
    }

    public function setOuterParticipant(ParticipantInterface $outerParticipant): void
    {
        self::$outerParticipantRefChild->setReferenceTargetElement($this, $outerParticipant);
    }
}
