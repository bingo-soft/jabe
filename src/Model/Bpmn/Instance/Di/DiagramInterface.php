<?php

namespace BpmPlatform\Model\Bpmn\Instance\Di;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface DiagramInterface extends BpmnModelElementInstanceInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getDocumentation(): string;

    public function setDocumentation(string $documentation): void;

    public function getResolution(): float;

    public function setResolution(float $resolution): void;

    public function getId(): string;

    public function setId(string $id): void;
}
