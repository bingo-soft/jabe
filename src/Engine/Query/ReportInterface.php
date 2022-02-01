<?php

namespace BpmPlatform\Engine\Query;

interface ReportInterface
{
    public function duration(string $periodUnit): array;
}
