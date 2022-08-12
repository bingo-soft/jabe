<?php

namespace Jabe\Impl\Db;

interface ResultHandlerInterface
{
    public function handleResult(ResultContextInterface $resultContext): void;
}
