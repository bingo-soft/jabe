<?php

namespace BpmPlatform\Engine\Impl\Calendar;

use BpmPlatform\Engine\Task\TaskInterface;

interface BusinessCalendarInterface
{
    public function resolveDuedate(string $duedateDescription, ?string $startDate, ?TaskInterface $task, ?int $repeatOffset = 0): ?\DateTime;
}
