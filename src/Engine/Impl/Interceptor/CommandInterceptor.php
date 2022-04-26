<?php

namespace Jabe\Engine\Impl\Interceptor;

abstract class CommandInterceptor implements CommandExecutorInterface
{
    /** will be initialized by the {@link CommandInterceptorChains} */
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
