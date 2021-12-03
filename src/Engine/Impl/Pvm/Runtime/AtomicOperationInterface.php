<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime;

interface AtomicOperationInterface
{
    public function isAsyncCapable(): bool;
}
