<?php

namespace Jabe\Query;

interface ReportInterface
{
    public function duration(string $periodUnit): array;
}
