<?php

namespace Tests\Impl\JobExecutor;

use Concurrent\Executor\DefaultPoolExecutor;
use Concurrent\Queue\ArrayBlockingQueue;
use Jabe\Impl\JobExecutor\DefaultJobExecutor;
use PHPUnit\Framework\TestCase;

class DefaultJobExecutorTest extends TestCase
{
    public function testCreatesPoolWithCurrentConstructorSignature(): void
    {
        $executor = new TestableDefaultJobExecutor();
        $queue = new ArrayBlockingQueue(7);

        $pool = $executor->createPool($queue);

        $this->assertInstanceOf(DefaultPoolExecutor::class, $pool);
        $this->assertSame(6, $pool->getCorePoolSize());
        $this->assertSame(18, $pool->getMaximumPoolSize());
        $this->assertSame($queue, $pool->getQueue());

        $keepAliveTime = new \ReflectionProperty(DefaultPoolExecutor::class, 'keepAliveTime');
        $keepAliveTime->setAccessible(true);
        $this->assertSame(0, $keepAliveTime->getValue($pool));
    }
}

class TestableDefaultJobExecutor extends DefaultJobExecutor
{
    public function createPool(ArrayBlockingQueue $queue): DefaultPoolExecutor
    {
        return $this->createThreadPoolExecutor($queue);
    }
}
