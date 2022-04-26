<?php

namespace Jabe\Model\Bpmn\Instance;

interface DocumentationInterface extends BpmnModelElementInstanceInterface
{
    public function getId(): string;

    public function setId(string $id): void;

    public function getTextFormat(): string;

    public function setTextFormat(string $textFormat): void;
}
