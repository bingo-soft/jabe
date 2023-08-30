<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Impl\Delegate\DelegateInvocation;

interface DelegateInterceptorInterface
{
    public function handleInvocation(DelegateInvocation $invocation): void;
}
