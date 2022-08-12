<?php

namespace Tests\WSDL;

use PHPUnit\Framework\TestCase;
use Jabe\Impl\Util\ClockUtil;
use Jabe\Impl\Calendar\DurationBusinessCalendar;

class DurationBusinessCalendarTest extends TestCase
{
    protected function tearDown(): void
    {
        ClockUtil::reset();
    }

    public function testSimpleDuration(): void
    {
        $businessCalendar = new DurationBusinessCalendar();

        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d H:i", "2010-06-11 17:23"));

        $duedate = $businessCalendar->resolveDuedate("P2DT5H70M");
        $this->assertEquals(\DateTime::createFromFormat("Y-m-d H:i", "2010-06-13 23:33"), $duedate);
    }

    public function testSimpleDurationWithStartDate(): void
    {
        $businessCalendar = new DurationBusinessCalendar();

        $now = \DateTime::createFromFormat("Y-m-d H:i", "2010-06-11 17:23");

        $duedate = $businessCalendar->resolveDuedate("P2DT5H70M", $now);
        $this->assertEquals(\DateTime::createFromFormat("Y-m-d H:i", "2010-06-13 23:33"), $duedate);
    }
}
