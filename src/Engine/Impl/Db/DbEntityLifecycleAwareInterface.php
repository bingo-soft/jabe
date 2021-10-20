<?php

namespace BpmPlatform\Engine\Impl\Db;

interface DbEntityLifecycleAwareInterface
{
    public function postLoad(): void;
}
