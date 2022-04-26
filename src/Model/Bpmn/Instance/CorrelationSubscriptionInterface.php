<?php

namespace Jabe\Model\Bpmn\Instance;

interface CorrelationSubscriptionInterface extends BaseElementInterface
{
    public function getCorrelationKey(): CorrelationKeyInterface;

    public function setCorrelationKey(CorrelationKeyInterface $correlationKey): void;

    public function getCorrelationPropertyBindings(): array;
}
