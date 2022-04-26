<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\ManualTaskBuilder;

interface ManualTaskInterface extends TaskInterface
{
    public function builder(): ManualTaskBuilder;
}
