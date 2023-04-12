<?php

namespace Jabe\Impl\Interceptor;

interface CommandExecutorInterface
{
    public function execute(CommandInterface $command, ...$args);

    public function setState(...$args): void;

    public function getState(): array;
}
