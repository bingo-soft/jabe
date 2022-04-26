<?php

namespace Jabe\Engine\Impl\Calendar;

use Jabe\Engine\Task\TaskInterface;

interface BusinessCalendarInterface
{
    public function resolveDuedate(string $duedateDescription, ?string $startDate, ?TaskInterface $task, ?int $repeatOffset = 0): ?\DateTime;
}
