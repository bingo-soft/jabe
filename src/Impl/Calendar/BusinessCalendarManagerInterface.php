<?php

namespace Jabe\Impl\Calendar;

interface BusinessCalendarManagerInterface
{
    public function getBusinessCalendar(string $businessCalendarRef): ?BusinessCalendarInterface;
}
