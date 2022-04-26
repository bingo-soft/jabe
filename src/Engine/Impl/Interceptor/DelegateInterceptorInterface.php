<?php

namespace Jabe\Engine\Impl\Interceptor;

use Jabe\Engine\Impl\Delegate\DelegateInvocation;

interface DelegateInterceptorInterface
{
    public function handleInvocation(DelegateInvocation $invocation): void;
}
