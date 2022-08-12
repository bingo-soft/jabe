<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\History\ReportResultInterface;

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
