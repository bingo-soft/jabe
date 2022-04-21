<?php

namespace BpmPlatform\Engine\Impl\Webservice;

interface SyncWebServiceClientFactory
{
    public function create(string $wsdl): SyncWebServiceClientInterface;
}
