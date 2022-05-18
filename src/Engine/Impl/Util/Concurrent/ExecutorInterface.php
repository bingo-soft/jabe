<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

interface ExecutorInterface
{
    /**
     * Executes the given command at some time in the future.  The command
     * may execute in a new process, in a pooled process, or in the calling
     * process, at the discretion of the <tt>Executor</tt> implementation.
     *
     * @param command the runnable task
     */
    public function execute(RunnableInterface $command): void;
}
