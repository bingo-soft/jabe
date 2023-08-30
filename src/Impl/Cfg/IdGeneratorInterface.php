<?php

namespace Jabe\Impl\Cfg;

interface IdGeneratorInterface
{
    public function getNextId(...$args): ?string;
}
