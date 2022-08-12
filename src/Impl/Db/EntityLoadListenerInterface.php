<?php

namespace Jabe\Impl\Db;

interface EntityLoadListenerInterface
{
    public function onEntityLoaded(DbEntityInterface $entity): void;
}
