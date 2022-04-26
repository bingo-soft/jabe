<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\AbstractGatewayBuilder;
use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

interface GatewayInterface extends FlowNodeInterface
{
    public function builder(): AbstractGatewayBuilder;

    public function getGatewayDirection(): string;

    public function setGatewayDirection(string $gatewayDirection): void;

    public function getDiagramElement(): BpmnShapeInterface;
}
