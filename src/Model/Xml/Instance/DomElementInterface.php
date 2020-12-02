<?php

namespace BpmPlatform\Model\Xml\Instance;

interface DomElementInterface
{
    public function getNamespaceURI(): string;

    public function getLocalName(): string;

    public function getPrefix(): string;

    public function getDocument(): DomDocumentInterface;

    public function getRootElement(): DomElementInterface;

    public function getChildElements(): array;

    public function getChildElementsByNameNs(array $namespaceUris, string $elementName): array;

    public function getChildElementsByType(
        ModelInstanceImpl $modelInstance,
        ModelElementInstanceInterface $elementType
    ): array;

    public function replaceChild(
        DomElementInterface $newChildDomElement,
        DomElementInterface $existingChildDomElement
    ): void;

    public function removeChild(DomElementInterface $domElement): bool;

    public function appendChild(DomElementInterface $childElement): void;

    public function insertChildElementAfter(
        DomElementInterface $elementToInsert,
        DomElementInterface $insertAfter
    ): void;

    public function hasAttribute(?string $namespaceUri, string $localName): bool;

    public function getAttribute(?string $namespaceUri, string $localName): string;

    public function setAttribute(?string $namespaceUri, string $localName, string $value): void;

    public function setIdAttribute(?string $namespaceUri, string $localName, string $value): void;

    public function removeAttribute(?string $namespaceUri, string $localName): void;

    public function getTextContent(): string;

    public function setTextContent(string $textContent): void;

    public function addCDataSection(string $data): void;

    public function getModelElementInstance(): ModelElementInstanceInterface;

    public function setModelElementInstance(ModelElementInstanceInterface $modelElementInstance): void;

    public function registerNamespace(?string $prefix, string $namespaceUri): string;

    public function lookupPrefix(string $namespaceUri): string;
}
