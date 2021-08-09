<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ValueInterface extends BpmnModelElementInstanceInterface
{
    public function getId(): string;

    public function setId(string $id): void;

    public function getName(): string;

    public function setName(string $name): void;
}
