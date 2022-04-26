<?php

namespace Jabe\Engine\Impl\Db;

interface DbEntityLifecycleAwareInterface
{
    public function postLoad(): void;
}
