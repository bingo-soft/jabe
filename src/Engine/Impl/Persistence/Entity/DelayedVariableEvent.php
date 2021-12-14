<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Core\Variable\Event\VariableEvent;
use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

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
