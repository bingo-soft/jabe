<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\InclusiveGatewayBuilder;

interface InclusiveGatewayInterface extends GatewayInterface
{
    public function builder(): InclusiveGatewayBuilder;

    public function getDefault(): SequenceFlowInterface;

    public function setDefault(SequenceFlowInterface $defaultFlow): void;
}
