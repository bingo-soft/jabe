<?php

namespace Jabe\Model\Bpmn\Instance;

interface ImportInterface extends BpmnModelElementInstanceInterface
{
    public function getNamespace(): string;

    public function setNamespace(string $namespace): void;

    public function getLocation(): string;

    public function setLocation(string $location): void;

    public function getImportType(): string;

    public function setImportType(string $importType): void;
}
