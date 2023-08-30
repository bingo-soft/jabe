<?php

namespace Jabe\Impl\Db\EntityManager;

interface RecyclableInterface
{
    public function recycle(): void;
}
