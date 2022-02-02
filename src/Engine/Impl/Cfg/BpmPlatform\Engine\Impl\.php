<?php

namespace BpmPlatform\Engine\Impl\Cfg;

interface IdGeneratorInterface
{
    public function getNextId(): string;
}
