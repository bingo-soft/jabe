<?php

namespace BpmPlatform\Engine\Impl\Webservice;

interface SyncWebServiceClientInterface
{
    public function send(string $methodName, array $arguments, array $overriddenEndpointAddresses);
}
