<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface MessageFlowAssociationInterface extends BaseElementInterface
{
    public function getInnerMessageFlow(): MessageFlowInterface;

    public function setInnerMessageFlow(MessageFlowInterface $innerMessageFlow): void;

    public function getOuterMessageFlow(): MessageFlowInterface;

    public function setOuterMessageFlow(MessageFlowInterface $outerMessageFlow): void;
}
