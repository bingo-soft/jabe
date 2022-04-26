<?php

namespace Jabe\Engine\Impl\Db;

interface EntityLoadListenerInterface
{
    public function onEntityLoaded(DbEntityInterface $entity): void;
}
