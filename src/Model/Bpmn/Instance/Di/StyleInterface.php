<?php

namespace Jabe\Model\Bpmn\Instance\Di;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface StyleInterface extends BpmnModelElementInstanceInterface
{
    public function getId(): string;

    public function setId(string $id): void;
}
