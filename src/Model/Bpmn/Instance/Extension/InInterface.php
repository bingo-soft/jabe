<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface InInterface extends BpmnModelElementInstanceInterface
{
    public function getSource(): ?string;

    public function setSource(string $source): void;

    public function getSourceExpression(): ?string;

    public function setSourceExpression(string $sourceExpression): void;

    public function getVariables(): ?string;

    public function setVariables(string $variables): void;

    public function getBusinessKey(): ?string;

    public function setBusinessKey(string $businessKey): void;

    public function getLocal(): bool;

    public function setLocal(bool $local): void;
}
