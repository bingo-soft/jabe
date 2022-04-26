<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\EndEventBuilder;

interface EndEventInterface extends ThrowEventInterface
{
    public function builder(): EndEventBuilder;
}
