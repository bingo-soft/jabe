<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\IntermediateCatchEventBuilder;

interface IntermediateCatchEventInterface extends CatchEventInterface
{
    public function builder(): IntermediateCatchEventBuilder;
}
