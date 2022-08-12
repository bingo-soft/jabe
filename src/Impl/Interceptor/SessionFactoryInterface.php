<?php

namespace Jabe\Impl\Interceptor;

interface SessionFactoryInterface
{
    public function getSessionType(): string;

    public function openSession(): SessionInterface;
}
