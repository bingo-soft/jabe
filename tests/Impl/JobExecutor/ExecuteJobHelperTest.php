<?php

namespace Tests\Impl\JobExecutor;

use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Impl\Interceptor\CommandInterface;
use Jabe\Impl\JobExecutor\ExecuteJobHelper;
use Jabe\Impl\JobExecutor\FailedJobListener;
use Jabe\Impl\JobExecutor\JobFailureCollector;
use PHPUnit\Framework\TestCase;

class ExecuteJobHelperTest extends TestCase
{
    public function testFailedJobListenerRetriesAreBoundedForPhpError(): void
    {
        $executor = new class () implements CommandExecutorInterface {
            public int $attempts = 0;

            public function execute(\Jabe\Impl\Interceptor\CommandInterface $command, ...$args)
            {
                $this->attempts += 1;
                throw new \Error('incident failure');
            }

            public function setState(...$args): void
            {
            }

            public function getState(): array
            {
                return [];
            }
        };

        $listener = $this->getMockBuilder(FailedJobListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $exception = TestableExecuteJobHelper::callFailedJobListener($executor, $listener);

        $this->assertInstanceOf(\Error::class, $exception);
        $this->assertSame('incident failure', $exception->getMessage());
        $this->assertSame(3, $executor->attempts);
        $this->assertSame(0, $listener->getRetriesLeft());
    }

    public function testHandledJobFailureDoesNotEscapeWorkerBoundary(): void
    {
        $delegateFailure = new \Error('delegate failure');
        $executor = new JobFailureCommandExecutor($delegateFailure);
        $collector = new JobFailureCollector('job-id');

        ExecuteJobHelper::executeJobInWorker(
            'job-id',
            $executor,
            $collector,
            new FailingJobCommand()
        );

        $this->assertSame($delegateFailure, $collector->getFailure());
        $this->assertSame(1, $executor->failedJobListenerCalls);
    }

    public function testManualJobExecutionStillRethrowsHandledFailure(): void
    {
        $delegateFailure = new \Error('delegate failure');
        $executor = new JobFailureCommandExecutor($delegateFailure);

        try {
            ExecuteJobHelper::executeJob(
                'job-id',
                $executor,
                new JobFailureCollector('job-id'),
                new FailingJobCommand()
            );
            $this->fail('Expected the original job failure to be rethrown');
        } catch (\Throwable $throwable) {
            $this->assertSame($delegateFailure, $throwable);
        }
    }

    public function testFailedJobListenerFailureEscapesWorkerBoundary(): void
    {
        $delegateFailure = new \Error('delegate failure');
        $listenerFailure = new \RuntimeException('failed-job persistence failure');
        $executor = new JobFailureCommandExecutor($delegateFailure, $listenerFailure);

        try {
            ExecuteJobHelper::executeJobInWorker(
                'job-id',
                $executor,
                new JobFailureCollector('job-id'),
                new FailingJobCommand()
            );
            $this->fail('Expected failed-job listener failure to escape worker boundary');
        } catch (\Throwable $throwable) {
            $this->assertSame($listenerFailure, $throwable);
        }
    }
}

class FailingJobCommand implements CommandInterface
{
    public function execute(\Jabe\Impl\Interceptor\CommandContext $commandContext, ...$args)
    {
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}

class JobFailureCommandExecutor implements CommandExecutorInterface
{
    private \Throwable $delegateFailure;
    private ?\Throwable $listenerFailure;
    public int $failedJobListenerCalls = 0;

    public function __construct(\Throwable $delegateFailure, ?\Throwable $listenerFailure = null)
    {
        $this->delegateFailure = $delegateFailure;
        $this->listenerFailure = $listenerFailure;
    }

    public function execute(CommandInterface $command, ...$args)
    {
        if ($command instanceof FailingJobCommand) {
            throw $this->delegateFailure;
        }

        if ($command instanceof FailedJobListener) {
            $this->failedJobListenerCalls += 1;
            if ($this->listenerFailure !== null) {
                throw $this->listenerFailure;
            }
            return null;
        }

        return null;
    }

    public function setState(...$args): void
    {
    }

    public function getState(): array
    {
        return [];
    }
}

class TestableExecuteJobHelper extends ExecuteJobHelper
{
    public static function callFailedJobListener(
        CommandExecutorInterface $commandExecutor,
        FailedJobListener $failedJobListener
    ): ?\Throwable {
        return parent::callFailedJobListenerWithRetries($commandExecutor, $failedJobListener);
    }
}
