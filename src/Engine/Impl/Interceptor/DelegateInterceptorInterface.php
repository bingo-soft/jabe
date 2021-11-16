<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

use BpmPlatform\Engine\Impl\Delegate\DelegateInvocation;

interface DelegateInterceptorInterface
{
    public function handleInvocation(DelegateInvocation $invocation): void;
}
