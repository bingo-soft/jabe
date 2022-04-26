<?php

namespace Jabe\Engine\Impl\Calendar;

interface BusinessCalendarManagerInterface
{
    public function getBusinessCalendar(string $businessCalendarRef): ?BusinessCalendarInterface;
}
