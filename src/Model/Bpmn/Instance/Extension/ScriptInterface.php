<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ScriptInterface extends BpmnModelElementInstanceInterface
{
    public function getScriptFormat(): string;

    public function setScriptFormat(string $scriptFormat): void;

    public function getResource(): ?string;

    public function setResource(string $resource): void;
}
