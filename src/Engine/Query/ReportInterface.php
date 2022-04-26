<?php

namespace Jabe\Engine\Query;

interface ReportInterface
{
    public function duration(string $periodUnit): array;
}
