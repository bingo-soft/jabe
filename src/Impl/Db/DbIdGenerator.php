<?php

namespace Jabe\Impl\Db;

use Jabe\Impl\Cfg\IdGeneratorInterface;
use Jabe\Impl\Cmd\GetNextIdBlockCmd;
use Jabe\Impl\Interceptor\CommandExecutorInterface;

class DbIdGenerator implements IdGeneratorInterface
{
    protected $idBlockSize;
    protected $nextId;
    protected $lastId;

    protected $commandExecutor;

    public function __construct()
    {
        $this->reset();
    }

    public function getNextId(): string
    {
        if ($this->lastId < $this->nextId) {
            $this->getNewBlock();
        }
        $this->nextId += 1;
        $_nextId = $this->nextId;
        return strval($_nextId);
    }

    protected function getNewBlock(): void
    {
        // TODO http://jira.codehaus.org/browse/ACT-45 use a separate 'requiresNew' command executor
        $idBlock = $this->commandExecutor->execute(new GetNextIdBlockCmd($this->idBlockSize));
        $this->nextId = $idBlock->getNextId();
        $this->lastId = $idBlock->getLastId();
    }

    public function getIdBlockSize(): int
    {
        return $this->idBlockSize;
    }

    public function setIdBlockSize(int $idBlockSize): void
    {
        $this->idBlockSize = $idBlockSize;
    }

    public function getCommandExecutor(): CommandExecutorInterface
    {
        return $this->commandExecutor;
    }

    public function setCommandExecutor(CommandExecutorInterface $commandExecutor): void
    {
        $this->commandExecutor = $commandExecutor;
    }

    /**
     * Reset inner state so that the generator fetches a new block of IDs from the database
     * when the next ID generation request is received.
     */
    public function reset(): void
    {
        $this->nextId = 0;
        $this->lastId = -1;
    }
}
