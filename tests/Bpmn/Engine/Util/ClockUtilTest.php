<?php

namespace Tests\Bpmn\Engine\Util;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Engine\Impl\Util\ClockUtil;

class ClockUtilTest extends TestCase
{
    private const ONE_SECOND = 1.0;
    private const TWO_SECONDS = 2.0;
    private const FIVE_SECONDS = 5.0;
    private const TWO_DAYS = 172800.0;

    protected function setUp(): void
    {
        ClockUtil::reset();
    }

    public static function resetClock(): void
    {
        ClockUtil::reset();
    }

    public function testShouldReturnCurrentTime(): void
    {
        $now = ClockUtil::now();
        $other = (new \DateTime('NOW'));
        $this->assertEqualsWithDelta($now, $other, self::ONE_SECOND);
    }

    public function testCurrentTimeShouldReturnSameValueAsNow(): void
    {
        $now = ClockUtil::getCurrentTime();
        $other = (new \DateTime('NOW'));
        $this->assertEqualsWithDelta($now, $other, self::ONE_SECOND);
    }

    public function testOffsetShouldTravelInTime(): void
    {
        $duration = self::TWO_DAYS;
        $target = new \DateTime();
        $target->setTimestamp($target->getTimestamp() + $duration);

        ClockUtil::offset($duration * 1000);

        sleep(1);

        $now = ClockUtil::now();
        $this->assertEqualsWithDelta($now, $target, self::TWO_SECONDS);
    }

    public function testSetCurrentTimeShouldFreezeTime(): void
    {
        $duration = self::TWO_DAYS;
        $target = new \DateTime();
        $target->setTimestamp($target->getTimestamp() + $duration);

        ClockUtil::setCurrentTime($target);

        usleep(1100000);

        $now = ClockUtil::now();
        $this->assertEqualsWithDelta($now, $target, self::ONE_SECOND);
    }

    public function testResetClockShouldResetToCurrentTime(): void
    {
        $duration = self::TWO_DAYS;
        $target = new \DateTime();
        $target->setTimestamp($target->getTimestamp() + $duration);

        ClockUtil::offset($duration * 1000);

        $now = ClockUtil::now();
        $this->assertEqualsWithDelta($now, $target, self::ONE_SECOND);

        $this->assertEqualsWithDelta(ClockUtil::resetClock(), new \DateTime(), self::ONE_SECOND);
        $this->assertEqualsWithDelta(ClockUtil::getCurrentTime(), new \DateTime(), self::ONE_SECOND);
    }

    public function testResetShouldResetToCurrentTime(): void
    {
        $duration = self::TWO_DAYS;
        $target = new \DateTime();
        $target->setTimestamp($target->getTimestamp() + $duration);

        ClockUtil::offset($duration * 1000);

        $now = ClockUtil::now();
        $this->assertEqualsWithDelta($now, $target, self::ONE_SECOND);

        ClockUtil::reset();

        $now = ClockUtil::now();
        $this->assertEqualsWithDelta($now, new \DateTime(), self::ONE_SECOND);
    }

    public function testTimeShouldMoveOnAfterTravel(): void
    {
        $duration = self::TWO_DAYS;
        $target = new \DateTime();
        $target->setTimestamp($target->getTimestamp() + $duration);

        ClockUtil::offset($duration * 1000);

        $now = ClockUtil::now();
        $this->assertEqualsWithDelta($now, $target, self::ONE_SECOND);

        sleep(self::FIVE_SECONDS);

        $now = ClockUtil::now();
        $other = new \DateTime();
        $other->setTimestamp($target->getTimestamp() + self::FIVE_SECONDS);
        $this->assertEqualsWithDelta($now, $other, self::ONE_SECOND);
    }

    public function testTimeShouldFreezeWithSetCurrentTime(): void
    {
        $duration = self::TWO_DAYS;
        $target = new \DateTime('NOW');
        $target->setTimestamp($target->getTimestamp() + $duration);

        ClockUtil::setCurrentTime($target);

        sleep(self::FIVE_SECONDS);

        $now = ClockUtil::now();
        $this->assertEqualsWithDelta($now, $target, self::ONE_SECOND);
    }
}
