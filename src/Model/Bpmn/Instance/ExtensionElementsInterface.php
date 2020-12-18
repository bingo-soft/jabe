<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\QueryInterface;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ExtensionElementsInterface extends BpmnModelElementInstanceInterface
{
    public function getElements(): array;

    public function getElementsQuery(): QueryInterface;

    public function addExtensionElement(string $extensionElementClass): ModelElementInstanceInterface;

    public function addExtensionElementNs(
        string $namespaceUriOrElementClass,
        string $localName
    ): ModelElementInstanceInterface;
}
