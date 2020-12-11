<?php

namespace BpmPlatform\Model\Xml\Instance;

interface DomDocumentInterface
{
    public function getRootElement(): ?DomElementInterface;

    public function setRootElement(DomElementInterface $rootElement): void;

    public function createElement(string $namespaceUri, string $localName): DomElementInterface;

    public function getElementById(string $id): ?DomDocumentInterface;

    public function getElementsByNameNs(string $namespaceUri, string $localName): array;

    public function registerNamespace(?string $prefix, string $namespaceUri): void;

    public function clone(): DomDocumentInterface;

    public function getDomSource(): \DOMDocument;
}
