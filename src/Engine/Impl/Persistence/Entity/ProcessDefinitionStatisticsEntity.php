<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Management\{
    IncidentStatisticsInterface,
    ProcessDefinitionStatisticsInterface
};
use Jabe\Engine\Impl\Util\ClassNameUtil;

class ProcessDefinitionStatisticsEntity extends ProcessDefinitionEntity implements ProcessDefinitionStatisticsInterface
{
    protected $instances;
    protected $failedJobs;
    protected $incidentStatistics;

    public function getInstances(): int
    {
        return $this->instances;
    }

    public function setInstances(int $instances): void
    {
        $this->instances = $instances;
    }

    public function getFailedJobs(): int
    {
        return $this->failedJobs;
    }

    public function setFailedJobs(int $failedJobs): void
    {
        $this->failedJobs = $failedJobs;
    }

    public function getIncidentStatistics(): array
    {
        return $this->incidentStatistics;
    }

    public function setIncidentStatistics(array $incidentStatistics): void
    {
        $this->incidentStatistics = $incidentStatistics;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[instances=" . $this->instances
                . ", failedJobs=" . $this->failedJobs
                . ", id=" . $this->id
                . ", deploymentId=" . $this->deploymentId
                . ", description=" . $this->description
                . ", historyLevel=" . $this->historyLevel
                . ", category=" . $this->category
                . ", hasStartFormKey=" . $this->hasStartFormKey
                . ", diagramResourceName=" . $this->diagramResourceName
                . ", key=" . $this->key
                . ", name=" . $this->name
                . ", resourceName=" . $this->resourceName
                . ", revision=" . $this->revision
                . ", version=" . $this->version
                . ", suspensionState=" . $this->suspensionState
                . "]";
    }
}
