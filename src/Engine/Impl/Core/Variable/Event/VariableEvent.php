<?php

namespace Jabe\Engine\Impl\Core\Variable\Event;

use Jabe\Engine\Impl\Core\Variable\Scope\AbstractVariableScope;
use Jabe\Engine\Runtime\VariableInstanceInterface;

class VariableEvent
{
    protected $variableInstance;
    protected $eventName;
    protected $sourceScope;

    public function __construct(VariableInstanceInterface $variableInstance, string $eventName, AbstractVariableScope $sourceScope)
    {
        $this->variableInstance = $variableInstance;
        $this->eventName = $eventName;
        $this->sourceScope = $sourceScope;
    }

    public function getVariableInstance(): VariableInstanceInterface
    {
        return $this->variableInstance;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getSourceScope(): AbstractVariableScope
    {
        return $this->sourceScope;
    }
}
