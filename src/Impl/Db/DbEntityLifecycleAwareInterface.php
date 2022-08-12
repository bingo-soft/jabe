<?php

namespace Jabe\Impl\Db;

interface DbEntityLifecycleAwareInterface
{
    public function postLoad(): void;
}
