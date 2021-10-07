<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface OutInterface extends BpmnModelElementInstanceInterface
{
    public function getSource(): string;

    public function setSource(string $source): void;

    public function getSourceExpression(): string;

    public function setSourceExpression(string $sourceExpression): void;

    public function getVariables(): ?string;

    public function setVariables(string $variables): void;

    public function getTarget(): string;

    public function setTarget(string $target): void;

    public function getLocal(): bool;

    public function setLocal(bool $local): void;
}
