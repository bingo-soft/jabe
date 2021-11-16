<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

interface SessionFactoryInterface
{
    public function getSessionType(): string;

    public function openSession(): SessionInterface;
}
