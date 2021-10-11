<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\SubProcessBuilder;

interface SubProcessInterface extends ActivityInterface
{
    public function builder(): SubProcessBuilder;

    public function triggeredByEvent(): bool;

    public function setTriggeredByEvent(bool $triggeredByEvent): void;

    public function getLaneSets(): array;

    public function getFlowElements(): array;

    public function addFlowElement(FlowElementInterface $element): void;

    public function removeFlowElement(FlowElementInterface $element): void;

    public function getArtifacts(): array;
}
