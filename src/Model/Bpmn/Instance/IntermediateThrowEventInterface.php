<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\IntermediateThrowEventBuilder;

interface IntermediateThrowEventInterface extends ThrowEventInterface
{
    public function builder(): IntermediateThrowEventBuilder;
}
