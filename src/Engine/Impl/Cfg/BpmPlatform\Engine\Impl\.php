<?php

namespace Jabe\Engine\Impl\Cfg;

interface IdGeneratorInterface
{
    public function getNextId(): string;
}
