<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ConstraintInterface extends BpmnModelElementInstanceInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getConfig(): string;

    public function setConfig(string $config): void;
}
