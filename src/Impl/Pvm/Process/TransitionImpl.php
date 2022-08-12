<?php

namespace Jabe\Impl\Pvm\Process;

use Jabe\Delegate\ExecutionListenerInterface;
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmProcessDefinitionInterface,
    PvmTransitionInterface
};

class TransitionImpl extends CoreModelElement implements PvmTransitionInterface
{
    protected $source;
    protected $destination;

    protected $processDefinition;

    /** Graphical information: a list of waypoints: x1, y1, x2, y2, x3, y3, .. */
    protected $waypoints = [];

    public function __construct(string $id, ProcessDefinitionImpl $processDefinition)
    {
        parent::__construct($id);
        $this->processDefinition = $processDefinition;
    }

    public function getSource(): ActivityImpl
    {
        return $this->source;
    }

    public function setDestination(ActivityImpl $destination): void
    {
        $this->destination = $destination;
        $this->destination->addIncomingTransition($this);
    }

    public function addExecutionListener(ExecutionListenerInterface $executionListener): void
    {
        parent::addListener(ExecutionListenerInterface::EVENTNAME_TAKE, $executionListener);
    }

    public function getExecutionListeners(): array
    {
        return parent::getListeners(ExecutionListenerInterface::EVENTNAME_TAKE);
    }

    public function setExecutionListeners(array $executionListeners): void
    {
        foreach ($executionListeners as $executionListener) {
            $this->addExecutionListener($executionListener);
        }
    }

    public function __toString()
    {
        return "(" . $this->source->getId() . ")--" . ($this->id !== null ? $this->id . "-->(" : ">(") . $this->destination->getId() . ")";
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getProcessDefinition(): PvmProcessDefinitionInterface
    {
        return $this->processDefinition;
    }

    protected function setSource(ActivityImpl $source): void
    {
        $this->source = $source;
    }

    public function getDestination(): PvmActivityInterface
    {
        return $this->destination;
    }

    public function getWaypoints(): array
    {
        return $this->waypoints;
    }

    public function setWaypoints(array $waypoints): void
    {
        $this->waypoints = $waypoints;
    }
}
