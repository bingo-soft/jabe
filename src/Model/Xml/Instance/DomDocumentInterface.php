<?php

namespace BpmPlatform\Model\Xml\Instance;

use DOMDocument;

interface DomDocumentInterface
{
    public function getRootElement(): DomElementInterface;

    public function setRootElement(DomElementInterface $rootElement): void;

    public function createElement(string $namespaceUri, string $localName): DomElementInterface;

    public function getElementById(string $id): DomElementInterface;

    public function getElementsByNameNs(string $namespaceUri, string $localName): array;

    public function getDomSource(): DOMDocument;

    public function registerNamespace(?string $prefix, string $namespaceUri): void;

    public function clone(): DomDocumentInterface;
}
