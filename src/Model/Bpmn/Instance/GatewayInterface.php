<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\AbstractGatewayBuilder;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;

interface GatewayInterface extends FlowNodeInterface
{
    public function builder(): AbstractGatewayBuilder;

    public function getGatewayDirection(): string;

    public function setGatewayDirection(string $gatewayDirection): void;

    public function getDiagramElement(): BpmnShapeInterface;
}
