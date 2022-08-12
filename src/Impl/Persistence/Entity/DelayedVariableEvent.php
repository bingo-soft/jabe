<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Impl\Pvm\Runtime\PvmExecutionImpl;

class DelayedVariableEvent
{
    protected $targetScope;
    protected $event;

    public function __construct(PvmExecutionImpl $targetScope, VariableEvent $event)
    {
        $this->targetScope = $targetScope;
        $this->event = $event;
    }

    public function getTargetScope(): PvmExecutionImpl
    {
        return $this->targetScope;
    }

    public function getEvent(): VariableEvent
    {
        return $this->event;
    }
}
