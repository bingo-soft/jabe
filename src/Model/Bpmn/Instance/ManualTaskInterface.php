<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\ManualTaskBuilder;

interface ManualTaskInterface extends TaskInterface
{
    public function builder(): ManualTaskBuilder;
}
