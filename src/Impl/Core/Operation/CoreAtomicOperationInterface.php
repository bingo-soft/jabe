<?php

namespace Jabe\Impl\Core\Operation;

use Jabe\Impl\Core\Instance\CoreExecution;

interface CoreAtomicOperationInterface
{
    public function execute(CoreExecution $instance): void;

    public function isAsync(CoreExecution $instance): bool;

    public function getCanonicalName(): ?string;
}
