<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ConnectorInterface extends BpmnModelElementInstanceInterface
{
    public function getConnectorId(): ConnectorIdInterface;

    public function setConnectorId(ConnectorIdInterface $connectorId): void;

    public function getInputOutput(): InputOutputInterface;

    public function setInputOutput(InputOutputInterface $inputOutput): void;
}
