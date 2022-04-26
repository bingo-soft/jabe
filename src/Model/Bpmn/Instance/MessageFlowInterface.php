<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

interface MessageFlowInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getSource(): InteractionNodeInterface;

    public function setSource(InteractionNodeInterface $source): void;

    public function getTarget(): InteractionNodeInterface;

    public function setTarget(InteractionNodeInterface $target): void;

    public function getMessage(): MessageInterface;

    public function setMessage(MessageInterface $message): void;

    public function getDiagramElement(): BpmnEdgeInterface;
}
