<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\IntermediateCatchEventBuilder;

interface IntermediateCatchEventInterface extends CatchEventInterface
{
    public function builder(): IntermediateCatchEventBuilder;
}
