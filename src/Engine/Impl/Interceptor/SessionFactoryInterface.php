<?php

namespace Jabe\Engine\Impl\Interceptor;

interface SessionFactoryInterface
{
    public function getSessionType(): string;

    public function openSession(): SessionInterface;
}
