<?php

namespace Jabe\Impl\Db;

interface ResultContextInterface
{
    public function getResultObject();

    public function getResultCount();

    public function isStopped(): bool;

    public function stop(): void;
}
