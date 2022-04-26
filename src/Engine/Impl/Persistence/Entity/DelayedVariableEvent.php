<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

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
