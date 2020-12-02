<?php

namespace BpmPlatform\Engine\Query;

interface ReportInterface
{
    public function duration(PeriodUnit $periodUnit): array;
}
