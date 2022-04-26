<?php

namespace Tests\Bpmn\Engine\Util;

use PHPUnit\Framework\TestCase;
use Jabe\Engine\Impl\Calendar\DefaultBusinessCalendar;
use Jabe\Engine\Impl\Util\ClockUtil;

class DefaultBusinessCalendarTest extends TestCase
{
    protected function tearDown(): void
    {
        ClockUtil::reset();
    }

    public function testSimpleDuration(): void
    {
        $businessCalendar = new DefaultBusinessCalendar();
        $now = new \DateTime("2011-06-11 17:23:00");
        ClockUtil::setCurrentTime($now);

        $duedate = $businessCalendar->resolveDuedate("1 day");

        $this->assertEquals(new \DateTime("2011-06-12 17:23:00"), $duedate);

        $duedate = $businessCalendar->resolveDuedate("2 days");

        $this->assertEquals(new \DateTime("2011-06-13 17:23:00"), $duedate);
    }
}
