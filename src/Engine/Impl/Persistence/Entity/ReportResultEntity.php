<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\ReportResultInterface;

abstract class ReportResultEntity implements ReportResultInterface
{
    protected $period;
    protected $periodUnit;

    public function getPeriod(): int
    {
        return $this->period;
    }

    public function setPeriod(int $period): void
    {
        $this->period = $period;
    }

    public function getPeriodUnit(): string
    {
        return $this->periodUnit;
    }

    public function setPeriodUnit(string $periodUnit): void
    {
        $this->periodUnit = $periodUnit;
    }
}
