<?php

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use Jabe\Engine\Impl\Util\Concurrent\{
    ProcessQueue,
    InterruptibleProcess,
    LockSupport,
    ProcessPoolExecutor,
    RunnableInterface,
    TimeUnit,
    Worker
};
use Swoole\Coroutine as Co;

class PoolExecutorTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testBlockingQueue(): void
    {
        $queue = new ProcessQueue(3);
        $queue->add(1);
        $queue->add(2);
        $queue->add(3);
        $this->assertEquals(3, $queue->size());
        $it = $queue->iterator();
        $this->assertEquals(1, $it->current());
        $this->assertEquals(1, $it->current());
        $this->assertTrue($it->valid());
        while ($it->valid()) {
            $it->next();
        }
        $this->assertEquals(3, $it->current());
        $this->assertFalse($it->valid());

        $queue->clear();
        $this->assertEquals(0, $queue->size());
        $queue->add(1);
        $this->assertEquals(1, $queue->size());
        $queue->remove(2);
        $this->assertEquals(1, $queue->size());
        $queue->remove(1);
        $this->assertEquals(0, $queue->size());
        $queue->add(2);
        $queue->add(3);
        $ar = $queue->toArray();
        $this->assertCount(2, $ar);

        echo \Swoole\Coroutine::getCid();
    }
}
