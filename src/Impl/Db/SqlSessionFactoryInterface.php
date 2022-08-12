<?php

namespace Jabe\Impl\Db;

interface SqlSessionFactoryInterface
{
    public function openSession($param = null): SqlSessionInterface;
}
