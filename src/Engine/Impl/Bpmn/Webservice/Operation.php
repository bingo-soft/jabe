<?php

namespace Jabe\Engine\Impl\Bpmn\Webservice;

use Jabe\Engine\Impl\Bpmn\Parser\MessageDefinition;

class Operation
{
    protected $id;

    protected $name;

    protected $inMessage;

    protected $outMessage;

    protected $implementation;

    /**
     * The interface to which this operations belongs
     */
    protected $bpmnInterface;

    public function __construct(string $id = null, string $name = null, BpmnInterface $bpmnInterface = null, MessageDefinition $inMessage = null)
    {
        if ($id !== null) {
            $this->setId($id);
            $this->setName($name);
            $this->setInterface($bpmnInterface);
            $this->setInMessage($inMessage);
        }
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getInterface(): BpmnInterface
    {
        return $this->bpmnInterface;
    }

    public function setInterface(BpmnInterface $bpmnInterface): void
    {
        $this->bpmnInterface = $bpmnInterface;
    }

    public function getInMessage(): ?MessageDefinition
    {
        return $this->inMessage;
    }

    public function setInMessage(MessageDefinition $inMessage): void
    {
        $this->inMessage = $inMessage;
    }

    public function getOutMessage(): ?MessageDefinition
    {
        return $this->outMessage;
    }

    public function setOutMessage(MessageDefinition $outMessage): void
    {
        $this->outMessage = $outMessage;
    }

    public function getImplementation(): ?OperationImplementationInterface
    {
        return $this->implementation;
    }

    public function setImplementation(OperationImplementationInterface $implementation): void
    {
        $this->implementation = $implementation;
    }
}
