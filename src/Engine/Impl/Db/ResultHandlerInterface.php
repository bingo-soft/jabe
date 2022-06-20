<?php

namespace Jabe\Engine\Impl\Db;

interface ResultHandlerInterface
{
    public function handleResult(ResultContextInterface $resultContext): void;
}
