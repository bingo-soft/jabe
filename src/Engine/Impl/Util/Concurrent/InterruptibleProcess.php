<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

class InterruptibleProcess extends \Swoole\Process
{
    private $interrupted = false;

    public function interrupt(): void
    {
        $this->interrupted = true;
        $this->close();
    }

    public function isInterrupted(): bool
    {
        return $this->interrupted;
    }
}
