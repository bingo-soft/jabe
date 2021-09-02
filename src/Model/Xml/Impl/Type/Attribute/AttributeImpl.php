<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Attribute;

use BpmPlatform\Model\Xml\Type\Attribute\AttributeInterface;
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\Reference\ReferenceInterface;

abstract class AttributeImpl implements AttributeInterface
{
    private $attributeName;

    private $namespaceUri;

    private $defaultValue;

    private $isRequired = false;

    private $isIdAttribute = false;

    private $outgoingReferences = [];

    private $incomingReferences = [];

    private $owningElementType;

    public function __construct(ModelElementTypeInterface $owningElementType)
    {
        $this->owningElementType = $owningElementType;
    }

    /**
     * @return mixed
     */
    abstract protected function convertXmlValueToModelValue(?string $rawValue);

    /**
     * @param mixed $modelValue;
     */
    abstract protected function convertModelValueToXmlValue($modelValue): string;

    public function getOwningElementType(): ModelElementTypeInterface
    {
        return $this->owningElementType;
    }

    /**
     * @return mixed
     */
    public function getValue(ModelElementInstanceInterface $modelElement)
    {
        if ($this->namespaceUri == null) {
            $value = $modelElement->getAttributeValue($this->attributeName);
        } else {
            $value = $modelElement->getAttributeValueNs($this->namespaceUri, $this->attributeName);
            if ($value == null) {
                $alternativeNamespaces = $this->owningElementType->getModel()
                                             ->getAlternativeNamespaces($this->namespaceUri);
                foreach ($alternativeNamespaces as $namespace) {
                    $value = $modelElement->getAttributeValueNs($namespace, $this->attributeName);
                    if ($value != null) {
                        break;
                    }
                }
            }
        }

        if ($value == null && $this->defaultValue != null) {
            return $this->defaultValue;
        } else {
            return $this->convertXmlValueToModelValue($value);
        }
    }

    /**
     * @param ModelElementInstanceInterface $modelElement
     * @param mixed $value
     * @param bool|null $withReferenceUpdate
     */
    public function setValue(
        ModelElementInstanceInterface $modelElement,
        $value,
        ?bool $withReferenceUpdate = false
    ): void {
        if ($withReferenceUpdate == null) {
            $withReferenceUpdate = true;
        }
        $xmlValue = $this->convertModelValueToXmlValue($value);
        if ($this->namespaceUri == null) {
            $modelElement->setAttributeValue(
                $this->attributeName,
                $xmlValue,
                $this->idIdAttribute,
                $withReferenceUpdate
            );
        } else {
            $modelElement->setAttributeValueNs(
                $this->namespaceUri,
                $this->attributeName,
                $xmlValue,
                $this->idIdAttribute,
                $withReferenceUpdate
            );
        }
    }

    public function updateIncomingReferences(
        ModelElementInstanceInterface $modelElement,
        string $newIdentifier,
        string $oldIdentifier
    ): void {
        foreach ($this->incomingReferences as $incomingReference) {
            $incomingReference->referencedElementUpdated($modelElement, $oldIdentifier, $newIdentifier);
        }
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setRequired(bool $required): void
    {
        $this->isRequired = $required;
    }

    public function setNamespaceUri(string $namespaceUri): void
    {
        $this->namespaceUri = $namespaceUri;
    }

    public function getNamespaceUri(): string
    {
        return $this->namespaceUri;
    }

    public function isIdAttribute(): bool
    {
        return $this->isIdAttribute;
    }

    public function setId(): void
    {
        $this->isIdAttribute = true;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function setAttributeName(string $attributeName): void
    {
        $this->attributeName = $attributeName;
    }

    public function removeAttribute(ModelElementInstanceInterface $modelElement): void
    {
        if ($this->namespaceUri == null) {
            $modelElement->removeAttribute($this->attributeName);
        } else {
            $modelElement->removeAttributeNs($this->namespaceUri, $this->attributeName);
        }
    }

    public function getIncomingReferences(): array
    {
        return $this->incomingReferences;
    }

    public function getOutgoingReferences(): array
    {
        return $this->outgoingReferences;
    }

    public function registerOutgoingReference(ReferenceInterface $ref): void
    {
        $this->outgoingReferences[] = $ref;
    }

    public function registerIncoming(ReferenceInterface $ref): void
    {
        $this->incomingReferences[] = $ref;
    }
}
