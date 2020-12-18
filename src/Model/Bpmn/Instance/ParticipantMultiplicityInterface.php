<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ParticipantMultiplicityInterface extends BaseElement
{
    public function getMinimum(): int;

    public function setMinimum(imt $minimum): void;

    public function getMaximum(): int;

    public function setMaximum(int $maximum): void;
}
