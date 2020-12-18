<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ParticipantInterface extends BaseElementInterface, InteractionNodeInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getProcess(): ProcessInterface;

    public function setProcess(ProcessInterface $process): void;

    public function getInterfaces(): array;

    public function getEndPoints(): array;

    public function getParticipantMultiplicity(): ParticipantMultiplicityInterface;

    public function setParticipantMultiplicity(ParticipantMultiplicityInterface $participantMultiplicity): void;
}
