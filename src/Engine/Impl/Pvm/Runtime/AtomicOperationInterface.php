<?php

namespace Jabe\Engine\Impl\Pvm\Runtime;

interface AtomicOperationInterface
{
    public function isAsyncCapable(): bool;
}
