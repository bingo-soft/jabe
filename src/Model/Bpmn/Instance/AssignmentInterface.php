<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Impl\Instance\{
    From,
    To
};

interface AssignmentInterface extends BaseElementInterface
{
    public function getForm(): From;

    public function setFrom(From $from): void;

    public function getTo(): To;

    public function setTo(To $to): void;
}
