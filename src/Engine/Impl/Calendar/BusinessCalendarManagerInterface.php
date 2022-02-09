<?php

namespace BpmPlatform\Engine\Impl\Calendar;

interface BusinessCalendarManagerInterface
{
    public function getBusinessCalendar(string $businessCalendarRef): ?BusinessCalendarInterface;
}
