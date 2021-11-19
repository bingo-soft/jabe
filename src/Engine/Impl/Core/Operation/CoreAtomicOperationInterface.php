<?php

namespace BpmPlatform\Engine\Impl\Core\Operation;

use BpmPlatform\Engine\Impl\Core\Instance\CoreExecution;

interface CoreAtomicOperationInterface
{
    public function execute(CoreExecution $instance): void;

    public function isAsync(CoreExecution $instance): bool;

    public function getCanonicalName(): string;
}
