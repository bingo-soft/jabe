<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Management\{
    DeploymentStatisticsInterface,
    IncidentStatisticsInterface
};
use Jabe\Impl\Util\ClassNameUtil;

class DeploymentStatisticsEntity extends DeploymentEntity implements DeploymentStatisticsInterface
{
    protected int $instances = 0;
    protected int $failedJobs = 0;
    protected $incidentStatistics = [];

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
                . ", name=" . $this->name
                . ", resources=" . json_encode($this->resources)
                . ", deploymentTime=" . $this->deploymentTime
                . ", validatingSchema=" . $this->validatingSchema
                . ", isNew=" . $this->isNew
                . "]";
    }
}
