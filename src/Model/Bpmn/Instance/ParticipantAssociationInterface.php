<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ParticipantAssociationInterface extends BaseElementInterface
{
    public function getInnerParticipant(): ParticipantInterface;

    public function setInnerParticipant(ParticipantInterface $innerParticipant): void;

    public function getOuterParticipant(): ParticipantInterface;

    public function setOuterParticipant(ParticipantInterface $outerParticipant): void;
}
