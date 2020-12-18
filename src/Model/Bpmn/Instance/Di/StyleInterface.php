<?php

namespace BpmPlatform\Model\Bpmn\Instance\Di;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface StyleInterface extends BpmnModelElementInstanceInterface
{
    public function getId(): string;

    public function setId(string $id): void;
}
