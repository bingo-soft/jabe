<?php

namespace Jabe\Query;

interface NativeQueryInterface
{
    public function sql(string $sql): NativeQueryInterface;

    /**
     * @param string $name
     * @param mixed $value
     */
    public function parameter(string $name, $value): NativeQueryInterface;

    public function count(): int;

    /**
     * @return mixed
     */
    public function singleResult();

    public function list(): array;

    public function listPage(int $firstResult, int $maxResults): array;
}
