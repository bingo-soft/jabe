<?php

namespace Tests\Impl\JobExecutor;

use Concurrent\Executor\DefaultPoolExecutor;
use Concurrent\Queue\ArrayBlockingQueue;
use Jabe\Impl\JobExecutor\DefaultJobExecutor;
use Jabe\Impl\JobExecutor\ExecuteJobsRunnable;
use Jabe\Impl\JobExecutor\JobAcquisitionContext;
use Jabe\Impl\JobExecutor\NotifyAcquisitionRejectedJobsHandler;
use Jabe\Impl\JobExecutor\RejectedJobsHandlerInterface;
use Jabe\Impl\JobExecutor\SequentialJobAcquisitionRunnable;
use Jabe\Impl\ProcessEngineImpl;
use Jabe\Impl\Interceptor\CommandExecutorInterface;
use PHPUnit\Framework\TestCase;

class SharedQueueCompatibilityTest extends TestCase
{
    public function testJabeRunnableCanBeQueuedWithConcurrent153(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $configuration = $engine->getProcessEngineConfiguration();
        $engine->method('getProcessEngineConfiguration')->willReturn($configuration);
        $configuration->method('getResource')->willReturn('test-engine.xml');

        $queue = new ArrayBlockingQueue(7);
        $executor = new DefaultPoolExecutor(6, 18, 0, \Concurrent\TimeUnit::MILLISECONDS, $queue);
        $this->assertSame($queue, $executor->getQueue());

        $jobIds = ['job-001', 'job-002', 'job-003'];
        $runnable = new ExecuteJobsRunnable($jobIds, $engine, ['Tests\\Impl\\JobExecutor\\SharedQueueCompatibilityTest', 'bootstrap']);
        $serialized = serialize($runnable);
        $this->assertLessThan(8192, strlen($serialized));
        $this->assertTrue($queue->offer($runnable));
        $this->assertSame($serialized, $queue->poll());
    }

    public function testDefaultExecutorQueueAcceptsTypicalJobBatch(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $engine->getProcessEngineConfiguration()->method('getResource')
            ->willReturn('engine.cfg.xml');
        $executor = new TestableSharedQueueJobExecutor();
        $queue = $executor->createDefaultQueueForTest();
        $pool = $executor->createPool($queue);

        $this->assertSame($queue, $pool->getQueue());
        $capacity = new \ReflectionProperty(ArrayBlockingQueue::class, 'capacity');
        $capacity->setAccessible(true);
        $this->assertSame(9999, $capacity->getValue($queue));
        $this->assertSame(3, $executor->getMaxJobsPerAcquisition());

        $jobIds = ['job-' . str_repeat('1', 36), 'job-' . str_repeat('2', 36), 'job-' . str_repeat('3', 36)];
        $runnable = new ExecuteJobsRunnable($jobIds, $engine, [self::class, 'bootstrap']);
        $this->assertLessThan(8192, strlen(serialize($runnable)));
        $this->assertTrue($queue->offer($runnable));
        $this->assertSame(1, $queue->size());
    }

    public function testSerializedJabeRunnableIsDeliveredToAnIdleProcessWorker(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $configuration = $engine->getProcessEngineConfiguration();
        $engine->method('getProcessEngineConfiguration')->willReturn($configuration);
        $configuration->method('getResource')->willReturn('test-engine.xml');
        $queue = new ArrayBlockingQueue(2);
        $pool = new DefaultPoolExecutor(1, 1, 0, \Concurrent\TimeUnit::MILLISECONDS, $queue);
        $resultFile = tempnam(sys_get_temp_dir(), 'jabe-queued-job-');
        putenv('JABE_QUEUED_JOB_RESULT_FILE=' . $resultFile);

        try {
            $pool->execute(new BlockingCompatibilityTask());
            $pool->execute(new DeliveredCompatibilityRunnable(['job-queued'], $engine));

            $deadline = microtime(true) + 5;
            while (file_get_contents($resultFile) === '' && microtime(true) < $deadline) {
                usleep(10000);
            }

            $this->assertSame("job-queued\n", file_get_contents($resultFile));
            $this->assertFalse($pool->isFailed());
        } finally {
            $pool->shutdown();
            putenv('JABE_QUEUED_JOB_RESULT_FILE');
            unlink($resultFile);
        }
    }

    public function testJabeRunnableWithLargeBatchIsRejectedBeforeQueueAdmission(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $engine->getProcessEngineConfiguration()->method('getResource')->willReturn('test-engine.xml');
        $queue = new ArrayBlockingQueue(7);
        new DefaultPoolExecutor(6, 18, 0, \Concurrent\TimeUnit::MILLISECONDS, $queue);

        $jobIds = array_map(static fn (int $number): string => 'job-' . str_pad((string) $number, 15, '0', STR_PAD_LEFT), range(1, 400));
        $runnable = new ExecuteJobsRunnable($jobIds, $engine, [self::class, 'bootstrap']);

        $this->expectException(\LengthException::class);
        $queue->offer($runnable);
    }

    public static function bootstrap(): void
    {
    }

    public function testSaturatedQueueRetainsRejectedJobBatchForNextAcquisition(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $engine->method('getName')->willReturn('test-engine');
        $executor = new TestableSharedQueueJobExecutor();
        $executor->setPoolSizes(6, 6);
        $queue = new ArrayBlockingQueue(1);
        $pool = $executor->createPool($queue);
        $executor->setThreadPoolExecutor($pool);
        $executor->setRejectedJobsHandler(new NotifyAcquisitionRejectedJobsHandler());
        $context = new JobAcquisitionContext();
        $executor->setAcquireRunnable(new TestableSharedQueueAcquisitionRunnable($context));

        for ($i = 0; $i < 6; ++$i) {
            $pool->execute(new BlockingCompatibilityTask());
        }

        $this->assertTrue($queue->offer('occupied'));
        $this->assertSame(1, $queue->size());
        $this->assertSame(6, $pool->getPoolSize());
        $executor->executeJobs(['job-123'], $engine);

        $this->assertSame([['job-123']], $context->getRejectedJobsByEngine()['test-engine'] ?? null);
        $context->reset();
        $this->assertSame([['job-123']], $context->getAdditionalJobsByEngine()['test-engine'] ?? null);
    }

    public function testOversizedExclusiveBatchIsNeverSplitOrPartiallyQueued(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $configuration = $engine->getProcessEngineConfiguration();
        $engine->method('getProcessEngineConfiguration')->willReturn($configuration);
        $configuration->method('getResource')->willReturn('test-engine.xml');
        $executor = new TestableSharedQueueJobExecutor();
        $executor->setPoolSizes(1, 1);
        $queue = new ArrayBlockingQueue(10);
        $pool = $executor->createPool($queue);
        $executor->setThreadPoolExecutor($pool);
        $pool->execute(new BlockingCompatibilityTask());
        $commandExecutor = $this->createMock(CommandExecutorInterface::class);
        $commandExecutor->expects($this->exactly(401))->method('execute');
        $configuration->method('getCommandExecutorTxRequired')->willReturn($commandExecutor);

        $jobIds = array_map(static fn (int $id): string => str_repeat('x', 32) . $id, range(1, 400));
        $executor->executeJobs($jobIds, $engine);
        $this->assertSame(0, $queue->size());
    }

    public function testSingleOversizedJobIdCannotBeSplitFurther(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $configuration = $engine->getProcessEngineConfiguration();
        $engine->method('getProcessEngineConfiguration')->willReturn($configuration);
        $configuration->method('getResource')->willReturn('test-engine.xml');
        $executor = new TestableSharedQueueJobExecutor();
        $executor->setPoolSizes(1, 1);
        $queue = new ArrayBlockingQueue(2);
        $pool = $executor->createPool($queue);
        $executor->setThreadPoolExecutor($pool);
        $pool->execute(new BlockingCompatibilityTask());
        $commandExecutor = $this->createMock(CommandExecutorInterface::class);
        $commandExecutor->expects($this->exactly(2))->method('execute');
        $configuration->method('getCommandExecutorTxRequired')->willReturn($commandExecutor);

        $executor->executeJobs([str_repeat('x', 9000)], $engine);
        $this->assertSame(0, $queue->size());
    }

    public function testSharedMutexFailureIsNotReportedAsCapacityRejection(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $executor = new TestableSharedQueueJobExecutor();
        $pool = $this->getMockBuilder(DefaultPoolExecutor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();
        $failure = new \RuntimeException('Shared worker queue mutex acquisition timed out');
        $pool->method('execute')->willThrowException($failure);
        $executor->setThreadPoolExecutor($pool);

        $handler = $this->createMock(RejectedJobsHandlerInterface::class);
        $handler->expects($this->never())->method('jobsRejected');
        $executor->setRejectedJobsHandler($handler);

        try {
            $executor->executeJobs(['job-123'], $engine);
            $this->fail('Expected the infrastructure failure to escape acquisition');
        } catch (\RuntimeException $actual) {
            $this->assertSame($failure, $actual);
        }
    }


    public function testFailedPoolIsNotReportedAsCapacityRejection(): void
    {
        $engine = $this->createMock(ProcessEngineImpl::class);
        $executor = new TestableSharedQueueJobExecutor();
        $pool = $this->getMockBuilder(DefaultPoolExecutor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();
        $failure = new \RuntimeException('Cannot execute tasks: worker pool has failed');
        $pool->method('execute')->willThrowException($failure);
        $executor->setThreadPoolExecutor($pool);

        $handler = $this->createMock(RejectedJobsHandlerInterface::class);
        $handler->expects($this->never())->method('jobsRejected');
        $executor->setRejectedJobsHandler($handler);

        try {
            $executor->executeJobs(['job-123'], $engine);
            $this->fail('Expected failed pool to remain an infrastructure error');
        } catch (\RuntimeException $actual) {
            $this->assertSame($failure, $actual);
        }
    }
}

class TestableSharedQueueJobExecutor extends DefaultJobExecutor
{
    public function createDefaultQueueForTest(): ArrayBlockingQueue
    {
        return new ArrayBlockingQueue($this->queueSize);
    }

    public function setPoolSizes(int $core, int $max): void
    {
        $this->corePoolSize = $core;
        $this->maxPoolSize = $max;
    }

    public function createPool(ArrayBlockingQueue $queue): DefaultPoolExecutor
    {
        return $this->createThreadPoolExecutor($queue);
    }

    public function setAcquireRunnable(SequentialJobAcquisitionRunnable $runnable): void
    {
        $this->acquireJobsRunnable = $runnable;
    }
}

class TestableSharedQueueAcquisitionRunnable extends SequentialJobAcquisitionRunnable
{
    public function __construct(private JobAcquisitionContext $context)
    {
    }

    public function getAcquisitionContext(): JobAcquisitionContext
    {
        return $this->context;
    }
}

class BlockingCompatibilityTask implements \Concurrent\RunnableInterface
{
    public function run(?\Concurrent\ThreadInterface $process = null, ...$args): void
    {
        usleep(3000000);
    }
}

class DeliveredCompatibilityRunnable extends ExecuteJobsRunnable
{
    public function run(?\Concurrent\ThreadInterface $process = null, ...$args): void
    {
        file_put_contents(getenv('JABE_QUEUED_JOB_RESULT_FILE'), $this->jobIds[0] . "\n", FILE_APPEND | LOCK_EX);
    }
}
