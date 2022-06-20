<?php

namespace Jabe\Engine\Impl\Db;

interface SqlSessionFactoryInterface
{
    public function openSession($param = null): SqlSessionInterface;
}
