<?php

namespace Jabe\Engine\Impl\Webservice;

interface SyncWebServiceClientInterface
{
    public function send(string $methodName, array $arguments, array $overriddenEndpointAddresses);
}
