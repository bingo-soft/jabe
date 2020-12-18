<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\EventBasedGatewayBuilder;

interface EventBasedGatewayInterface extends GatewayInterface
{
    public function builder(): EventBasedGatewayBuilder;

    public function isInstantiate(): bool;

    public function setInstantiate(bool $isInstantiate): void;

    public function getEventGatewayType(): string;

    public function setEventGatewayType(string $eventGatewayType): void;
}
