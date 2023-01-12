<?php

namespace Jabe\Impl\Webservice;

interface SyncWebServiceClientInterface
{
    public function send(?string $methodName, array $arguments, array $overriddenEndpointAddresses);
}
