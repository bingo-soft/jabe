<?php

namespace Jabe\Impl\Core\Variable\Event;

interface VariableEventDispatcherInterface
{
    public function dispatchEvent(VariableEvent $variableEvent): void;
}
