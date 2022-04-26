<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\InclusiveGatewayBuilder;

interface InclusiveGatewayInterface extends GatewayInterface
{
    public function builder(): InclusiveGatewayBuilder;

    public function getDefault(): SequenceFlowInterface;

    public function setDefault(SequenceFlowInterface $defaultFlow): void;
}
