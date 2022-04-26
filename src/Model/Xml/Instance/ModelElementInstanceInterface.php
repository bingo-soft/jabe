<?php

namespace Jabe\Model\Xml\Instance;

use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Type\ModelElementTypeInterface;

interface ModelElementInstanceInterface
{
    public function getDomElement(): ?DomElementInterface;

    public function getModelInstance(): ModelInstanceInterface;

    public function getParentElement(): ?ModelElementInstanceInterface;

    public function getElementType(): ModelElementTypeInterface;

    public function getAttributeValue(string $attributeName): ?string;

    public function setAttributeValue(
        string $attributeName,
        string $xmlValue,
        bool $isIdAttribute = false,
        bool $withReferenceUpdate = true
    ): void;

    public function removeAttribute(string $attributeName): void;

    public function getAttributeValueNs(string $namespaceUri, string $attributeName): ?string;

    public function setAttributeValueNs(
        string $namespaceUri,
        string $attributeName,
        string $xmlValue,
        bool $isIdAttribute = false,
        bool $withReferenceUpdate = true
    ): void;

    public function removeAttributeNs(string $namespaceUri, string $attributeName): void;

    public function getTextContent(): string;

    public function getRawTextContent(): string;

    public function setTextContent(string $textContent): void;

    public function replaceWithElement(ModelElementInstanceInterface $newElement): void;

    public function getUniqueChildElementByNameNs(
        string $namespaceUri,
        string $elementName
    ): ?ModelElementInstanceInterface;

    public function getUniqueChildElementByType(
        string $elementType
    ): ?ModelElementInstanceInterface;

    public function setUniqueChildElementByNameNs(ModelElementInstanceInterface $newChild): void;

    public function replaceChildElement(
        ModelElementInstanceInterface $existingChild,
        ModelElementInstanceInterface $newChild
    ): void;

    public function addChildElement(ModelElementInstanceInterface $newChild): void;

    public function removeChildElement(ModelElementInstanceInterface $child): bool;

    /**
     * @param mixed $childElementType
     */
    public function getChildElementsByType($childElementType): array;

    public function insertElementAfter(
        ModelElementInstanceInterface $elementToInsert,
        ?ModelElementInstanceInterface $insertAfterElement
    ): void;

    public function updateAfterReplacement(): void;

    public function equals(?ModelElementInstanceInterface $obj): bool;
}
