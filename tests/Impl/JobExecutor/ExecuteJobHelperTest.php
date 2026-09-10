<?php

namespace Tests\Impl\JobExecutor;

use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Impl\JobExecutor\ExecuteJobHelper;
use Jabe\Impl\JobExecutor\FailedJobListener;
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
