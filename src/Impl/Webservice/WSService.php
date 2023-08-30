<?php

namespace Jabe\Impl\Webservice;

use Jabe\Impl\Bpmn\Webservice\BpmnInterfaceImplementationInterface;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Util\ReflectUtil;

class WSService implements BpmnInterfaceImplementationInterface
{
    protected $name;

    protected $location;

    protected $operations = [];

    protected $wsdlLocation;

    protected $client;

    public function __construct(?string $name, ?string $location, $data)
    {
        $this->name = $name;
        $this->location = $location;
        $this->operations = [];
        if ($data instanceof SyncWebServiceClientInterface) {
            $this->client = $data;
        } elseif (is_string($data)) {
            $this->wsdlLocation = $data;
        }
    }

    public function addOperation(WSOperation $operation): void
    {
        $this->operations[$operation->getName()] = $operation;
    }

    public function getClient(): SyncWebServiceClientInterface
    {
        if ($this->client === null) {
            // TODO refactor to use configuration
            $factory = ReflectUtil::instantiate(ProcessEngineConfigurationImpl::DEFAULT_WS_SYNC_FACTORY);
            $this->client = $factory->create($this->wsdlLocation);
        }
        return $this->client;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }
}
