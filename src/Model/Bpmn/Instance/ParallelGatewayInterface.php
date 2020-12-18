<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\ParallelGatewayBuilder;

interface ParallelGatewayInterface extends GatewayInterface
{
    public function isAsync(): bool;

    public function setAsync(bool $isAsync): void;

    public function builder(): ParallelGatewayBuilder;
}
