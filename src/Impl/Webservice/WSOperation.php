<?php

namespace Jabe\Impl\Webservice;

use Jabe\Impl\Bpmn\Webservice\{
    Operation,
    OperationImplementationInterface
};

class WSOperation implements OperationImplementationInterface
{
    protected $id;

    protected $name;

    protected $service;

    public function __construct(?string $id, ?string $operationName, WSService $service)
    {
        $this->id = $id;
        $this->name = $operationName;
        $this->service = $service;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getService(): WSService
    {
        return $this->service;
    }
}
