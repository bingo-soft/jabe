<?php

namespace Tests\Bpmn\Engine\Util;

use PHPUnit\Framework\TestCase;
use Jabe\Engine\Impl\Calendar\DueDateBusinessCalendar;
use Jabe\Engine\Impl\Util\ClockUtil;

class DueDateBusinessCalendarTest extends TestCase
{
    protected function tearDown(): void
    {
        ClockUtil::reset();
    }

    public function testSimpleDuration(): void
    {
        $businessCalendar = new DueDateBusinessCalendar();
        $now = new \DateTime("2011-06-11 17:23:00");
        ClockUtil::setCurrentTime($now);

        $duedate = $businessCalendar->resolveDuedate("P1D");

        $this->assertEquals(new \DateTime("2011-06-12 17:23:00"), $duedate);

        $duedate = $businessCalendar->resolveDuedate("2011-06-15 17:23:00");

        $this->assertEquals(new \DateTime("2011-06-15 17:23:00"), $duedate);
    }
}
