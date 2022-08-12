<?php

namespace Tests\WSDL;

use PHPUnit\Framework\TestCase;
use Jabe\Impl\Util\ClockUtil;
use Jabe\Impl\Calendar\DurationHelper;

class DurationHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        ClockUtil::reset();
    }

    public function testShhouldNotExceedNumber(): void
    {
        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp(0));
        $dh = new DurationHelper("R2/PT10S");

        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp(15));
        $this->assertEquals(20, $dh->getDateAfter()->getTimestamp());

        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp(30));
        $this->assertNull($dh->getDateAfter());
    }

    public function testShouldNotExceedNumberPeriods(): void
    {
        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));
        $dh = new DurationHelper("R2/1970-01-01T00:00:00/1970-01-01T00:00:10");

        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:15"));

        $this->assertEquals(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:20"), $dh->getDateAfter());

        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:30"));
        $this->assertNull($dh->getDateAfter());
    }

    public function testShouldNotExceedNumberNegative(): void
    {
        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));
        $dh = new DurationHelper("R2/PT10S/1970-01-01T00:00:50");

        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:20"));
        $this->assertEquals(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:30"), $dh->getDateAfter());

        ClockUtil::setCurrentTime(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:35"));

        $this->assertEquals(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:40"), $dh->getDateAfter());
    }

    public function testShouldNotExceedNumberWithStartDate(): void
    {
        $dh = new DurationHelper("R2/PT10S", (new \DateTime())->setTimestamp(0));
        $this->assertEquals(20, $dh->getDateAfter((new \DateTime())->setTimestamp(15))->getTimestamp());
        $this->assertNull($dh->getDateAfter((new \DateTime())->setTimestamp(30)));
    }

    public function testShouldNotExceedNumberPeriodsWithStartDate(): void
    {
        $dh = new DurationHelper("R2/1970-01-01T00:00:00/1970-01-01T00:00:10", \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));
        $this->assertEquals(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:20"), $dh->getDateAfter(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:15")));
        $this->assertNull($dh->getDateAfter(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:30")));
    }

    public function testShouldNotExceedNumberNegativeWithStartDate(): void
    {
        $dh = new DurationHelper("R2/PT10S/1970-01-01T00:00:50", \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));

        $this->assertEquals(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:30"), $dh->getDateAfter(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:20")));

        $this->assertEquals(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:40"), $dh->getDateAfter(\DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:35")));
    }

    public function testShouldParseAllSupportedISO8601DurationPatterns(): void
    {
        // given
        // when
        $pnYnMnDTnHnMnS = new DurationHelper("P1Y5M21DT19H47M55S", \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));
        $pnW = new DurationHelper("P2W", \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));
        // then
        $this->assertEquals($pnYnMnDTnHnMnS->getDateAfter(), \DateTime::createFromFormat("Y-m-d-H:i:s", "1971-06-22-19:47:55"));
        $this->assertEquals($pnW->getDateAfter(), \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-15-00:00:00"));
    }

    public function testShouldParseP4W(): void
    {
        // given

        // when
        $pnW = new DurationHelper("P4W", \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));

        // then
        $this->assertEquals($pnW->getDateAfter(), \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-29-00:00:00"));
    }

    public function testShouldParseP5W(): void
    {
        // given

        // when
        $pnW = new DurationHelper("P5W", \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));

        // then
        $this->assertEquals($pnW->getDateAfter(), \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-02-05-00:00:00"));
    }

    public function testShouldParseP22W(): void
    {
        // given

        // when
        $pnW = new DurationHelper("P22W", \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-01-01-00:00:00"));

        // then
        $this->assertEquals($pnW->getDateAfter(), \DateTime::createFromFormat("Y-m-d-H:i:s", "1970-06-04-00:00:00"));
    }
}
