<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\EndEventBuilder;

interface EndEventInterface extends ThrowEventInterface
{
    public function builder(): EndEventBuilder;
}
