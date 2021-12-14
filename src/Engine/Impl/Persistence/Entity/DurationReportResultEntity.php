<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\DurationReportResultInterface;
use BpmPlatform\Engine\Impl\Util\ClassNameUtil;

class DurationReportResultEntity extends ReportResultEntity implements DurationReportResultInterface
{
    protected $minimum;
    protected $maximum;
    protected $average;

    public function getMinimum(): int
    {
        return $this->minimum;
    }

    public function setMinimum(int $minimum): void
    {
        $this->minimum = $minimum;
    }

    public function getMaximum(): int
    {
        return $this->maximum;
    }

    public function setMaximum(int $maximum): void
    {
        $this->maximum = $maximum;
    }

    public function getAverage(): int
    {
        return $this->average;
    }

    public function setAverage(int $average): void
    {
        $this->average = $average;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
            . "[period=" . $this->period
            . ", periodUnit=" . $this->periodUnit
            . ", minimum=" . $this->minimum
            . ", maximum=" . $this->maximum
            . ", average=" . $this->average
            . "]";
    }
}
