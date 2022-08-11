<?php

namespace Jabe\Engine\Impl\Calendar;

interface BusinessCalendarInterface
{
    public function resolveDuedate(string $duedateDescription, $startDate = null, int $repeatOffset = 0): ?\DateTime;
}
