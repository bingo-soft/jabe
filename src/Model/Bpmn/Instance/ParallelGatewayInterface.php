<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\ParallelGatewayBuilder;

interface ParallelGatewayInterface extends GatewayInterface
{
    public function builder(): ParallelGatewayBuilder;
}
