<?php

namespace Jabe\Impl\Bpmn\Webservice;

class BpmnInterface
{
    protected $id;

    protected $name;

    protected $implementation;

    /**
     * Mapping of the operations of this interface. The key of the map is the id of the operation, for easy retrieval.
     */
    protected $operations = [];

    public function __construct(?string $id = null, ?string $name = null)
    {
        if ($id !== null) {
            $this->setId($id);
            $this->setName($name);
        }
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function addOperation(Operation $operation): void
    {
        $this->operations[$operation->getId()] = $operation;
    }

    public function getOperation(?string $operationId): ?Operation
    {
        if (array_key_exists($operationId, $this->operations)) {
            return $this->operations[$operationId];
        }
        return null;
    }

    public function getOperations(): array
    {
        return array_values($this->operations);
    }

    public function getImplementation(): ?BpmnInterfaceImplementationInterface
    {
        return $this->implementation;
    }

    public function setImplementation(BpmnInterfaceImplementationInterface $implementation): void
    {
        $this->implementation = $implementation;
    }
}
