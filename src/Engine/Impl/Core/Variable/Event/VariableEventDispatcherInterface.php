<?php

namespace BpmPlatform\Engine\Impl\Core\Variable\Event;

interface VariableEventDispatcherInterface
{
    public function dispatchEvent(VariableEvent $variableEvent): void;
}
