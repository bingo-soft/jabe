<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface ExecutionListenerInterface extends BpmnModelElementInstanceInterface
{
    public function getEvent(): string;

    public function setEvent(string $event): void;

    public function getClass(): string;

    public function setClass(string $class): void;

    public function getExpression(): string;

    public function setExpression(string $sxpression): void;

    public function getDelegateExpression(): string;

    public function setDelegateExpression(string $delegateExpression): void;

    public function getFields(): array;

    public function getScript(): ScriptInterface;

    public function setScript(ScriptInterface $script);
}
