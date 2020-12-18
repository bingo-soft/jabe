<?php

namespace BpmPlatform\Model\Bpmn\Instance\Bpmndi;

use BpmPlatform\Model\Bpmn\Instance\BaseElementInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\LabeledShapeInterface;

interface BpmnShapeInterface extends LabeledShapeInterface
{
    public function getBpmnElement(): BaseElementInterface;

    public function setBpmnElement(BaseElementInterface $bpmnElement): void;

    public function isHorizontal(): bool;

    public function setHorizontal(bool $isHorizontal): void;

    public function isExpanded(): bool;

    public function setExpanded(bool $isExpanded): void;

    public function isMarkerVisible(): bool;

    public function setMarkerVisible(bool $isMarkerVisible): void;

    public function isMessageVisible(): bool;

    public function setMessageVisible(bool $isMessageVisible): void;

    public function getParticipantBandKind(): string;

    public function setParticipantBandKind(string $participantBandKind): void;

    public function getChoreographyActivityShape(): BpmnShapeInterface;

    public function setChoreographyActivityShape(BpmnShapeInterface $choreographyActivityShape): void;

    public function getBpmnLabel(): BpmnLabelInterface;

    public function setBpmnLabel(BpmnLabelInterface $bpmnLabel): void;
}
