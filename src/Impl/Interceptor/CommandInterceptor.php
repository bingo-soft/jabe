<?php

namespace Jabe\Impl\Interceptor;

abstract class CommandInterceptor implements CommandExecutorInterface
{
    /** will be initialized by the CommandInterceptorChains */
    protected $next;

    protected $jobExecutorState = [];

    public function getNext(): ?CommandExecutorInterface
    {
        return $this->next;
    }

    public function setNext(CommandExecutorInterface $next): void
    {
        $this->next = $next;
    }

    public function setState(...$args): void
    {
        $this->jobExecutorState = $args;
    }

    public function getState(): array
    {
        return $this->jobExecutorState;
    }
}
