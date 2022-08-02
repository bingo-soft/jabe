<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\{
    ActivityInstanceInterface,
    IncidentInterface,
    TransitionInstanceInterface
};

class ActivityInstanceImpl extends ProcessElementInstanceImpl implements ActivityInstanceInterface
{
    protected static $NO_ACTIVITY_INSTANCES = [];
    protected static $NO_TRANSITION_INSTANCES = [];

    protected $businessKey;
    protected $activityId;
    protected $activityName;
    protected $activityType;

    protected $childActivityInstances;
    protected $childTransitionInstances;

    protected $executionIds;
    protected $incidentIds;
    protected $incidents = [];

    public function __construct()
    {
        $this->childActivityInstances = self::$NO_ACTIVITY_INSTANCES;
        $this->childTransitionInstances = self::$NO_ACTIVITY_INSTANCES;
        $this->executionIds = self::$NO_IDS;
        $this->incidentIds = self::$NO_IDS;
    }

    public function getChildActivityInstances(): array
    {
        return $this->childActivityInstances;
    }

    public function setChildActivityInstances(array $childInstances): void
    {
        $this->childActivityInstances = $childInstances;
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function setBusinessKey(string $businessKey): void
    {
        $this->businessKey = $businessKey;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getExecutionIds(): array
    {
        return $this->executionIds;
    }

    public function setExecutionIds(array $executionIds): void
    {
        $this->executionIds = $executionIds;
    }

    public function getChildTransitionInstances(): array
    {
        return $this->childTransitionInstances;
    }

    public function setChildTransitionInstances(array $childTransitionInstances): void
    {
        $this->childTransitionInstances = $childTransitionInstances;
    }

    public function getActivityType(): string
    {
        return $this->activityType;
    }

    public function setActivityType(string $activityType): void
    {
        $this->activityType = $activityType;
    }

    public function getActivityName(): string
    {
        return $this->activityName;
    }

    public function setActivityName(string $activityName): void
    {
        $this->activityName = $activityName;
    }

    public function getIncidentIds(): array
    {
        return $this->incidentIds;
    }

    public function setIncidentIds(array $incidentIds): void
    {
        $this->incidentIds = $incidentIds;
    }

    public function getIncidents(): array
    {
        return $this->incidents;
    }

    public function setIncidents(array $incidents): void
    {
        $this->incidents = $incidents;
    }

    protected function writeTree(string &$writer, string $prefix, bool $isTail): void
    {
        $writer .= $prefix;
        if ($isTail) {
            $writer .= "└── ";
        } else {
            $writer .= "├── ";
        }

        $writer .= $this->getActivityId() . "=>"  . $this->getId() . "\n";

        for ($i = 0; $i < count($this->childTransitionInstances); $i += 1) {
            $transitionInstance = $this->childTransitionInstances[$i];
            $transitionIsTail = ($i == (count($this->childTransitionInstances) - 1)) && (count($this->childActivityInstances) == 0);
            $this->writeTransition($transitionInstance, $writer, $prefix  .  ($isTail ? "    " : "│   "), $transitionIsTail);
        }

        for ($i = 0; $i < count($this->childActivityInstances); $i += 1) {
            $child = $this->childActivityInstances[$i];
            $child->writeTree($writer, $prefix . ($isTail ? "    " : "│   "), ($i == (count($this->childActivityInstances) - 1)));
        }
    }

    protected function writeTransition(TransitionInstanceInterface $transition, string &$writer, string $prefix, bool $isTail): void
    {
        $writer .= $prefix;
        if ($isTail) {
            $writer .= "└── ";
        } else {
            $writer .= "├── ";
        }

        $writer .= "transition to/from " . $transition->getActivityId() . ":" . $transition->getId() . "\n";
    }

    public function __toString()
    {
        $writer = "";
        $this->writeTree($writer, "", true);
        return $writer;
    }

    public function getActivityInstances(string $activityId): array
    {
        EnsureUtil::ensureNotNull("activityId", "activityId", $activityId);

        $instances = [];
        $this->collectActivityInstances($activityId, $instances);

        return $instances;
    }

    protected function collectActivityInstances(string $activityId, array &$instances): void
    {
        if ($this->activityId == $activityId) {
            $instances[] = $this;
        } else {
            foreach ($this->childActivityInstances as $childInstance) {
                $childInstance->collectActivityInstances($activityId, $instances);
            }
        }
    }

    public function getTransitionInstances(string $activityId): array
    {
        EnsureUtil::ensureNotNull("activityId", "activityId", $activityId);

        $instances = [];
        $this->collectTransitionInstances($activityId, $instances);

        return $instances;
    }

    protected function collectTransitionInstances(string $activityId, array &$instances): void
    {
        $instanceFound = false;

        foreach ($this->childTransitionInstances as $childTransitionInstance) {
            if ($activityId == $childTransitionInstance->getActivityId()) {
                $instances[] = $childTransitionInstance;
                $instanceFound = true;
            }
        }

        if (!$instanceFound) {
            foreach ($this->childActivityInstances as $childActivityInstance) {
                $childActivityInstance->collectTransitionInstances($activityId, $instances);
            }
        }
    }
}
