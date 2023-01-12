<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Management\IncidentStatisticsInterface;
use Jabe\Impl\Util\ClassNameUtil;

class IncidentStatisticsEntity implements IncidentStatisticsInterface
{
    protected $incidentType;
    protected int $incidentCount = 0;

    public function getIncidentType(): ?string
    {
        return $this->incidentType;
    }

    public function setIncidenType(?string $incidentType): void
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
