<?php

namespace Jabe\Engine\Impl\Db\EntityManager;

interface RecyclableInterface
{
    public function recycle(): void;
}
