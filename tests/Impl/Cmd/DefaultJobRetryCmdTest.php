<?php

namespace Tests\Impl\Cmd;

use Jabe\Impl\Cmd\DefaultJobRetryCmd;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\JobEntity;
use PHPUnit\Framework\TestCase;

class DefaultJobRetryCmdTest extends TestCase
{
    public function testMissingProcessDefinitionDoesNotAbortFailedJobRecovery(): void
    {
        $job = $this->createMock(JobEntity::class);
        $job->method('getProcessDefinitionId')->willReturn(null);

        $command = new TestableDefaultJobRetryCmd($job);

        $this->assertNull($command->currentActivity($this->createMock(CommandContext::class)));
    }
}

class TestableDefaultJobRetryCmd extends DefaultJobRetryCmd
{
    private JobEntity $job;

    public function __construct(JobEntity $job)
    {
        parent::__construct('job-id', new \Error('job failure'));
        $this->job = $job;
    }

    public function currentActivity(CommandContext $context)
    {
        $method = new \ReflectionMethod(DefaultJobRetryCmd::class, 'getCurrentActivity');
        $method->setAccessible(true);
        return $method->invoke($this, $context, $this->job);
    }
}
