<?php

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use Jabe\Engine\Impl\Util\Concurrent\{
    ArrayBlockingQueue,
    InterruptibleProcess,
    ProcessPoolExecutor,
    RunnableInterface,
    TimeUnit,
    Worker
};

class PoolExecutorTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testBlockingQueue(): void
    {
        $queue = new ArrayBlockingQueue(3);
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
    }

    public function testTaskExecution(): void
    {
        $workQueue = new ArrayBlockingQueue(3);
        $pool = new ProcessPoolExecutor(3, 0, TimeUnit::SECONDS, $workQueue);
        $task1 = new TestTask("task 1");
        $task2 = new TestTask("task 2");
        $task3 = new TestTask("task 3");
        $task4 = new TestTask("task 4");
        $task5 = new TestTask("task 5");
        $task6 = new TestTask("task 6");
        $task7 = new TestTask("task 7");
        $task8 = new TestTask("task 8");
        $task9 = new TestTask("task 9");
        $pool->execute($task1);
        $pool->execute($task2);
        $pool->execute($task3);
        $pool->execute($task4);
        $pool->execute($task5);
        $pool->shutdown();
        $pool->execute($task6);
        $pool->execute($task7);
        $pool->execute($task8);
        $pool->execute($task9);
        $pool->shutdown();
        $this->assertTrue($pool->isShutdown());
    }
}
