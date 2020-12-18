<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\ExclusiveGatewayBuilder;

interface ExclusiveGatewayInterface extends GatewayInterface
{
    public function builder(): ExclusiveGatewayBuilder;

    public function getDefault(): SequenceFlowInterface;

    public function setDefault(SequenceFlowInterface $defaultFlow): void;
}
