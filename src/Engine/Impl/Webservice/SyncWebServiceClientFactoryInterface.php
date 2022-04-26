<?php

namespace Jabe\Engine\Impl\Webservice;

interface SyncWebServiceClientFactory
{
    public function create(string $wsdl): SyncWebServiceClientInterface;
}
