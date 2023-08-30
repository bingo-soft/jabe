<?php

namespace Jabe\Impl\Interceptor;

interface SessionInterface
{
    public function flush(): void;

    public function close(): void;
}
