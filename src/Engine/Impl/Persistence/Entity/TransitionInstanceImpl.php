<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Runtime\{
    IncidentInterface,
    TransitionInstanceInterface
};
use Jabe\Engine\Impl\Util\ClassNameUtil;

class TransitionInstanceImpl extends ProcessElementInstanceImpl implements TransitionInstanceInterface
{
    protected $executionId;
    protected $activityId;
    protected $activityName;
    protected $activityType;

    protected $incidentIds = [];
    protected $incidents = [];

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getTargetActivityId(): string
    {
        return $this->activityId;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function setExecutionId(string $executionId): void
    {
        $this->executionId = $executionId;
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

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[executionId=" . $this->executionId
                . ", targetActivityId=" . $this->activityId
                . ", activityName=" . $this->activityName
                . ", activityType=" . $this->activityType
                . ", id=" . $this->id
                . ", parentActivityInstanceId=" . $this->parentActivityInstanceId
                . ", processInstanceId=" . $this->processInstanceId
                . ", processDefinitionId=" . $this->processDefinitionId
                . ", incidentIds=" . json_encode($this->incidentIds)
                . ", incidents=" . json_encode($this->incidents)
                . "]";
    }
}
