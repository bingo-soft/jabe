<?php

namespace Jabe\Impl\Db;

class IdBlock
{
    private $nextId;
    private $lastId;

    public function __construct(int $nextId, int $lastId)
    {
        $this->nextId = $nextId;
        $this->lastId = $lastId;
    }

    public function getNextId(...$args): int
    {
        return $this->nextId;
    }
    public function getLastId(): int
    {
        return $this->lastId;
    }
}
