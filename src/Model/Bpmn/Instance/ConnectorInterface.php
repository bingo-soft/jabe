<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ConnectorInterface extends BpmnModelElementInstanceInterface
{
    public function getCamundaConnectorId(): ConnectorIdInterface;

    public function setCinnectorId(ConnectorIdInterface $connectorId): void;

    public function getInputOutput(): InputOutputInterface;

    public function setInputOutput(InputOutputInterface $inputOutput): void;
}
