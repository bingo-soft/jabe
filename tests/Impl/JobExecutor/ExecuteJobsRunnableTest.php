<?php

namespace Tests\Impl\JobExecutor;

use Jabe\Impl\JobExecutor\ExecuteJobsRunnable;
use Jabe\Impl\ProcessEngineImpl;
use PHPUnit\Framework\TestCase;

class ExecuteJobsRunnableTest extends TestCase
{
    public function testBootstrapFailureEscapesWorkerRunnable(): void
    {
        $bootstrapFailure = new \RuntimeException('bootstrap failure');
        $processEngine = $this->createMock(ProcessEngineImpl::class);
        $runnable = new ExecuteJobsRunnable([], $processEngine, static function () use ($bootstrapFailure): void {
            throw $bootstrapFailure;
        });

        $method = new \ReflectionMethod(ExecuteJobsRunnable::class, 'invokeJobExecutionBootstrap');
        $method->setAccessible(true);

        try {
            $method->invoke($runnable);
            $this->fail('Expected bootstrap failure to escape worker runnable');
        } catch (\ReflectionException $exception) {
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->assertSame($bootstrapFailure, $throwable);
        }
    }
}
