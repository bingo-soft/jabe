<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Management\IncidentStatisticsInterface;
use Jabe\Engine\Impl\Util\ClassNameUtil;

class IncidentStatisticsEntity implements IncidentStatisticsInterface
{
    protected $incidentType;
    protected $incidentCount;

    public function getIncidentType(): string
    {
        return $this->incidentType;
    }

    public function setIncidenType(string $incidentType): void
    {
        $this->incidentType = $incidentType;
    }

    public function getIncidentCount(): int
    {
        return $this->incidentCount;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[incidentType=" . $this->incidentType
                . ", incidentCount=" . $this->incidentCount
                . "]";
    }
}
