<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\IntermediateThrowEventBuilder;

interface IntermediateThrowEventInterface extends ThrowEventInterface
{
    public function builder(): IntermediateThrowEventBuilder;
}
