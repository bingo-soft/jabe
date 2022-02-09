<?php

namespace Tests\Bpmn\Engine\Util;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Engine\Impl\Calendar\DurationBusinessCalendar;
use BpmPlatform\Engine\Impl\Util\ClockUtil;

class DurationBusinessCalendarTest extends TestCase
{
    protected function tearDown(): void
    {
        ClockUtil::reset();
    }

    public function testSimpleDuration(): void
    {
        $businessCalendar = new DurationBusinessCalendar();
        $now = new \DateTime("2011-06-11 17:23:00");
        ClockUtil::setCurrentTime($now);

        $duedate = $businessCalendar->resolveDuedate("P1D");

        $this->assertEquals(new \DateTime("2011-06-12 17:23:00"), $duedate);
    }
}
