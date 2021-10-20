<?php

namespace BpmPlatform\Engine\Impl\Db;

interface EntityLoadListenerInterface
{
    public function onEntityLoaded(DbEntityInterface $entity): void;
}
