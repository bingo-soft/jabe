<?php

namespace BpmPlatform\Engine\Impl\Pvm\Process;

use BpmPlatform\Engine\Delegate\BaseDelegateExecutionInterface;
use BpmPlatform\Engine\Impl\Core\Delegate\CoreActivityBehaviorInterface;
use BpmPlatform\Engine\Impl\Pvm\{
    PvmProcessDefinitionInterface,
    PvmProcessInstanceInterface,
    PvmScopeInterface
};
use BpmPlatform\Engine\Impl\Pvm\Runtime\{
    ExecutionImpl,
    PvmExecutionImpl
};

class ProcessDefinitionImpl extends ScopeImpl implements PvmProcessDefinitionInterface
{
    protected $namename;
    protected $description;
    protected $initial;
    protected $initialActivityStacks = [];
    protected $laneSets = [];
    protected $participantProcess;

    public function __construct(string $id)
    {
        parent::__construct($id, null);
        $this->processDefinition = $this;
        // the process definition is always "a sub process scope"
        $this->isSubProcessScope = true;
    }

    protected function ensureDefaultInitialExists(): void
    {
        if ($this->initial == null) {
            throw new \Exception("Process '" . $this->name . "' has no default start activity (e.g. none start event), hence you cannot use 'startProcessInstanceBy...' but have to start it using one of the modeled start events (e.g. message start events)");
        }
    }

    public function createProcessInstance(?string $businessKey = null, ?string $caseInstanceId = null, ?ActivityImpl $initial = null): PvmProcessInstance
    {
        $this->ensureDefaultInitialExists();
        if ($initial == null) {
            $initial = $this->initial;
        }
        $processInstance = $this->createProcessInstanceForInitial($initial);

        $processInstance->setBusinessKey($businessKey);
        //processInstance.setCaseInstanceId(caseInstanceId);

        return $processInstance;
    }

    /** creates a process instance using the provided activity as initial */
    public function createProcessInstanceForInitial(ActivityImpl $initial): PvmProcessInstance
    {
        if ($initial == null) {
            throw new \Exception("Cannot start process instance, initial activity where the process instance should start is null");
        }

        $processInstance = $this->newProcessInstance();

        $processInstance->setStarting(true);
        $processInstance->setProcessDefinition($this);

        $processInstance->setProcessInstance($processInstance);

        // always set the process instance to the initial activity, no matter how deeply it is nested;
        // this is required for firing history events (cf start activity) and persisting the initial activity
        // on async start
        $processInstance->setActivity($initial);

        return $processInstance;
    }

    protected function newProcessInstance(): PvmExecutionImpl
    {
        return new ExecutionImpl();
    }

    public function getInitialActivityStack(?ActivityImpl $startActivity = null): array
    {
        foreach ($this->initialActivityStacks as $stack) {
            if ($stack[0] == $startActivity) {
                $initialActivityStack = $stack[1];
            }
        }
        if ($initialActivityStack == null) {
            $initialActivityStack = [];
            $activity = $startActivity;
            while ($activity != null) {
                array_unshift($initialActivityStack, $activity);
                $activity = $activity->getParentFlowScopeActivity();
            }
            $this->initialActivityStacks[] = [$startActivity, $initialActivityStack];
        }
        return $initialActivityStack;
    }

    public function getDiagramResourceName(): ?string
    {
        return null;
    }

    public function getDeploymentId(): ?string
    {
        return null;
    }

    public function addLaneSet(LaneSet $newLaneSet): void
    {
        $this->laneSets[] = $newLaneSet;
    }

    public function getLaneForId(string $id): ?Lane
    {
        foreach ($this->laneSets as $set) {
            $lane = $set->getLaneForId($id);
            if ($lane != null) {
                return $lane;
            }
        }
        return null;
    }

    public function getActivityBehavior(): ?CoreActivityBehaviorInterface
    {
        // unsupported in PVM
        return null;
    }

    // getters and setters //////////////////////////////////////////////////////
    public function getInitial(): ?ActivityImpl
    {
        return $this->initial;
    }

    public function setInitial(ActivityImpl $initial): void
    {
        $this->initial = $initial;
    }

    public function __toString()
    {
        return "ProcessDefinition(" . $this->id . ")";
    }

    public function getDescription(): ?string
    {
        return $this->getProperty("documentation");
    }

    /**
     * @return all lane-sets defined on this process-instance. Returns an empty list if none are defined.
     */
    public function getLaneSets(): array
    {
        return $this->laneSets;
    }

    public function setParticipantProcess(ParticipantProcess $participantProcess): void
    {
        $this->participantProcess = $participantProcess;
    }

    public function getParticipantProcess(): ?ParticipantProcess
    {
        return $this->participantProcess;
    }

    public function isScope(): bool
    {
        return true;
    }

    public function getEventScope(): ?PvmScopeInterface
    {
        return null;
    }

    public function getFlowScope(): ?ScopeImpl
    {
        return null;
    }

    public function getLevelOfSubprocessScope(): ?PvmScopeInterface
    {
        return null;
    }

    public function isSubProcessScope(): bool
    {
        return true;
    }
}
