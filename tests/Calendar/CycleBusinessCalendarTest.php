<?php

namespace Tests\WSDL;

use PHPUnit\Framework\TestCase;
use Jabe\Impl\Util\ClockUtil;
use Jabe\Impl\Calendar\CycleBusinessCalendar;

class CycleBusinessCalendarTest extends TestCase
{
    protected function tearDown(): void
    {
        ClockUtil::reset();
    }

    public function testSimpleCron(): void
    {
        $businessCalendar = new CycleBusinessCalendar();

        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d H:i", "2011-03-11 17:23"));

        $duedate = $businessCalendar->resolveDuedate("0 0 1 * ?");
        $this->assertEquals(\DateTime::createFromFormat("Y-m-d H:i", "2011-04-01 00:00"), $duedate);
    }

    public function testSimpleDuration(): void
    {
        $businessCalendar = new CycleBusinessCalendar();

        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d H:i", "2010-06-11 17:23"));

        $duedate = $businessCalendar->resolveDuedate("R/P2DT5H70M");
        $this->assertEquals(\DateTime::createFromFormat("Y-m-d H:i", "2010-06-13 23:33"), $duedate);
    }

    public function testSimpleCronWithStartDate(): void
    {
        $businessCalendar = new CycleBusinessCalendar();

        $now = \DateTime::createFromFormat("Y-m-d H:i", "2011-03-11 17:23");

        $duedate = $businessCalendar->resolveDuedate("0 0 1 * ?", $now);
        $this->assertEquals(\DateTime::createFromFormat("Y-m-d H:i", "2011-04-01 00:00"), $duedate);
    }

    public function testSimpleDurationWithStartDate(): void
    {
        $businessCalendar = new CycleBusinessCalendar();

        $now = \DateTime::createFromFormat("Y-m-d H:i", "2010-06-11 17:23");

        $duedate = $businessCalendar->resolveDuedate("R/P2DT5H70M", $now);
        $this->assertEquals(\DateTime::createFromFormat("Y-m-d H:i", "2010-06-13 23:33"), $duedate);
    }
}
