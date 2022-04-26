<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\ScriptTaskBuilder;

interface ScriptTaskInterface extends TaskInterface
{
    public function builder(): ScriptTaskBuilder;

    public function getScriptFormat(): string;

    public function setScriptFormat(string $scriptFormat): void;

    public function getScript(): ScriptInterface;

    public function getResultVariable(): string;

    public function setResultVariable(string $resultVariable): void;

    public function getResource(): string;

    public function setResource(string $resource): void;
}
