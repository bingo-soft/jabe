<?php

namespace Jabe\Query;

interface QueryInterface
{
    public function asc(): QueryInterface;

    public function desc(): QueryInterface;

    public function count(): int;

    /**
     * @return mixed
     */
    public function singleResult();

    public function list(): array;

    public function unlimitedList(): array;

    public function listPage(int $firstResult, int $maxResults): array;
}
