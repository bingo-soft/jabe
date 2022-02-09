<?php

namespace BpmPlatform\Engine\Impl\Calendar;

class MapBusinessCalendarManager implements BusinessCalendarManagerInterface
{
    private $businessCalendars = [];

    public function getBusinessCalendar(string $businessCalendarRef): ?BusinessCalendarInterface
    {
        if (array_key_exists($businessCalendarRef, $this->businessCalendars)) {
            return $this->businessCalendars[$businessCalendarRef];
        }
        return null;
    }

    public function addBusinessCalendar(string $businessCalendarRef, BusinessCalendarInterface $businessCalendar): BusinessCalendarManagerInterface
    {
        $this->businessCalendars[$businessCalendarRef] = $businessCalendar;
        return $this;
    }
}
