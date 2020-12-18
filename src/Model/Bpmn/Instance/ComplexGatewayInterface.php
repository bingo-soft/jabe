<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\ComplexGatewayBuilder;

interface ComplexGatewayInterface extends GatewayInterface
{
    public function builder(): ComplexGatewayBuilder;

    public function getDefault(): SequenceFlowInterface;

    public function setDefault(SequenceFlowInterface $defaultFlow): void;

    public function getActivationCondition(): ActivationConditionInterface;

    public function setActivationCondition(ActivationConditionInterface $activationCondition): void;
}
