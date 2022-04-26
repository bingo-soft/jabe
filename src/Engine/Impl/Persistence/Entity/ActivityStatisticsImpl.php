<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Management\{
    ActivityStatisticsInterface,
    IncidentStatisticsInterface
};
use Jabe\Engine\Impl\Util\ClassNameUtil;

class ActivityStatisticsImpl implements ActivityStatisticsInterface
{
    protected $id;
    protected $instances;
    protected $failedJobs;
    protected $incidentStatistics;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

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
             . "[id=" . $this->id
             . ", instances=" . $this->instances
             . ", failedJobs=" . $this->failedJobs
             . "]";
    }
}
