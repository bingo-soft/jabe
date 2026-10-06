<?php

namespace Tests\Impl\Db;

use Jabe\Impl\Db\{DbIdGenerator, IdBlock};
use Jabe\Impl\Interceptor\{CommandExecutorInterface, CommandInterface};
use PHPUnit\Framework\TestCase;

class DbIdGeneratorTest extends TestCase
{
    public function testRetriesOnlyTheIdBlockReservationAfterOptimisticConflict(): void
    {
        $executor = $this->createMock(CommandExecutorInterface::class);
        $calls = 0;
        $executor->method('execute')->willReturnCallback(function (CommandInterface $command) use (&$calls) {
            ++$calls;
            if ($calls < 3) {
                throw new \Jabe\ProcessEngineException(
                    'exception while executing command',
                    new \Jabe\OptimisticLockingException('concurrentUpdateDbEntityException: update PropertyEntity[next.dbid]')
                );
            }
            return new IdBlock(100, 109);
        });
        $executor->method('getState')->willReturn([]);

        $generator = new DbIdGenerator();
        $generator->setIdBlockSize(10);
        $generator->setCommandExecutor($executor);

        $this->assertSame('100', $generator->getNextId());
        $this->assertSame(3, $calls);
        $this->assertSame('101', $generator->getNextId());
    }

    public function testDoesNotRetryUnrelatedCommandFailure(): void
    {
        $executor = $this->createMock(CommandExecutorInterface::class);
        $executor->expects($this->once())->method('execute')->willThrowException(new \RuntimeException('business failure'));
        $executor->method('getState')->willReturn([]);

        $generator = new DbIdGenerator();
        $generator->setIdBlockSize(10);
        $generator->setCommandExecutor($executor);

        $this->expectException(\RuntimeException::class);
        $generator->getNextId();
    }
}
