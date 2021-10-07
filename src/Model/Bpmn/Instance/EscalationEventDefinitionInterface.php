<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface EscalationEventDefinitionInterface extends EventDefinitionInterface
{
    public function getEscalation(): ?EscalationInterface;

    public function setEscalation(EscalationInterface $escalation): void;
}
