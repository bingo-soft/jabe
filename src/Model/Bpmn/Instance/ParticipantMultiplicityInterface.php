<?php

namespace Jabe\Model\Bpmn\Instance;

interface ParticipantMultiplicityInterface extends BaseElementInterface
{
    public function getMinimum(): int;

    public function setMinimum(int $minimum): void;

    public function getMaximum(): int;

    public function setMaximum(int $maximum): void;
}
