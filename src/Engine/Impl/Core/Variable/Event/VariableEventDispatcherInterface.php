<?php

namespace Jabe\Engine\Impl\Core\Variable\Event;

interface VariableEventDispatcherInterface
{
    public function dispatchEvent(VariableEvent $variableEvent): void;
}
