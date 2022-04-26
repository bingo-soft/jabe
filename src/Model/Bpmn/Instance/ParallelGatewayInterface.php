<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\ParallelGatewayBuilder;

interface ParallelGatewayInterface extends GatewayInterface
{
    public function builder(): ParallelGatewayBuilder;
}
