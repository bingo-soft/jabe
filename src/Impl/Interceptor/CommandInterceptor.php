<?php

namespace Jabe\Impl\Interceptor;

abstract class CommandInterceptor implements CommandExecutorInterface
{
    /** will be initialized by the CommandInterceptorChains */
    protected $next;

    public function getNext(): ?CommandExecutorInterface
    {
        return $this->next;
    }

    public function setNext(CommandExecutorInterface $next): void
    {
        $this->next = $next;
    }
}
