<?php

namespace Jabe\Impl\Pvm\Runtime;

interface AtomicOperationInterface
{
    public function isAsyncCapable(): bool;
}
