<?php

namespace Jabe\Impl\Webservice;

interface SyncWebServiceClientFactory
{
    public function create(?string $wsdl): SyncWebServiceClientInterface;
}
