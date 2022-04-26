<?php

namespace Tests\Bpmn\Engine\Util;

use PHPUnit\Framework\TestCase;
use Jabe\Engine\Impl\Calendar\CycleBusinessCalendar;
use Jabe\Engine\Impl\Util\ClockUtil;

class CycleBusinessCalendarTest extends TestCase
{
    protected function tearDown(): void
    {
        ClockUtil::reset();
    }

    public function testSimpleCron(): void
    {
        $businessCalendar = new CycleBusinessCalendar();
        $now = new \DateTime("2011-03-11 17:23:00");
        ClockUtil::setCurrentTime($now);
        $duedate = $businessCalendar->resolveDuedate("0 0 1 * ?");
        $this->assertEquals(new \DateTime("2011-04-01 00:00:00"), $duedate);
    }

    public function testSimpleDuration(): void
    {
        $businessCalendar = new CycleBusinessCalendar();
        $now = new \DateTime("2011-06-11 17:23:00");
        ClockUtil::setCurrentTime($now);

        $duedate = $businessCalendar->resolveDuedate("R/P2DT5H70M");

        $this->assertEquals(new \DateTime("2011-06-13 23:33:00"), $duedate);
    }

    public function testSimpleCronWithStartDate(): void
    {
        $businessCalendar = new CycleBusinessCalendar();

        $now = new \DateTime("2011-03-11 17:23:00");

        $duedate = $businessCalendar->resolveDuedate("0 0 1 * ?", $now);

        $this->assertEquals(new \DateTime("2011-04-01 00:00:00"), $duedate);
    }

    public function testSimpleDurationWithStartDate(): void
    {
        $businessCalendar = new CycleBusinessCalendar();
        $now = new \DateTime("2011-06-11 17:23:00");

        $duedate = $businessCalendar->resolveDuedate("R/P2DT5H70M", $now);

        $this->assertEquals(new \DateTime("2011-06-13 23:33:00"), $duedate);
    }
}
