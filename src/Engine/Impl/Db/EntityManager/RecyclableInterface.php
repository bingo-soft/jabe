<?php

namespace BpmPlatform\Engine\Impl\Db\EntityManager;

interface RecyclableInterface
{
    public function recycle(): void;
}
