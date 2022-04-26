<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface PropertyInterface extends BpmnModelElementInstanceInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function getValue(): string;

    public function setValue(string $value): void;
}
