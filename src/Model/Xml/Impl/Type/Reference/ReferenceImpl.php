<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Reference;

use BpmPlatform\Model\Xml\Type\Reference\ReferenceInterface;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Exception\ModelReferenceException;
use BpmPlatform\Model\Xml\Type\Attribute\AttributeInterface;
use BpmPlatform\Model\Xml\Impl\Type\Attribute\AttributeImpl;
use BpmPlatform\Model\Xml\Impl\Type\ModelElementTypeImpl;

abstract class ReferenceImpl implements ReferenceInterface
{
    protected $referenceTargetAttribute;

    private $referenceTargetElementType;

    abstract protected function setReferenceIdentifier(
        ModelElementInstanceInterface $referenceSourceElement,
        string $referenceIdentifier
    ): void;

    /**
     * @return mixed
     */
    public function getReferenceTargetElement(ModelElementInstanceInterface $referenceSourceElement)
    {
        $identifier = $this->getReferenceIdentifier($referenceSourceElement);
        $referenceTargetElement = $referenceSourceElement->getModelInstance()->getModelElementById($identifier);
        return $referenceTargetElement;
    }

    /**
     * @param ModelElementInstanceInterface $referenceSourceElement
     * @param mixed $referenceTargetElement
     */
    public function setReferenceTargetElement(
        ModelElementInstanceInterface $referenceSourceElement,
        $referenceTargetElement
    ): void {
        $modelInstance = $referenceSourceElement->getModelInstance();
        $referenceTargetIdentifier = $this->referenceTargetAttribute->getValue($referenceTargetElement);
        $xml = $modelInstance->getDocument()->getDomSource()->saveXML();
        $existingElement = $modelInstance->getModelElementById($referenceTargetIdentifier);
        if ($existingElement == null || !$existingElement->equals($referenceTargetElement)) {
            throw new ModelReferenceException("Cannot create reference to model element");
        } else {
            $this->setReferenceIdentifier($referenceSourceElement, $referenceTargetIdentifier);
        }
    }

    public function setReferenceTargetAttribute(AttributeImpl $referenceTargetAttribute): void
    {
        $this->referenceTargetAttribute = $referenceTargetAttribute;
    }

    public function getReferenceTargetAttribute(): AttributeInterface
    {
        return $this->referenceTargetAttribute;
    }

    public function setReferenceTargetElementType(ModelElementTypeImpl $referenceTargetElementType): void
    {
        $this->referenceTargetElementType = $referenceTargetElementType;
    }

    public function findReferenceSourceElements(ModelElementInstanceInterface $referenceTargetElement): array
    {
        if ($this->referenceTargetElementType->isBaseTypeOf($referenceTargetElement->getElementType())) {
            $owningElementType = $this->getReferenceSourceElementType();
            return $referenceTargetElement->getModelInstance()->getModelElementsByType($owningElementType);
        } else {
            return [];
        }
    }

    abstract protected function updateReference(
        ModelElementInstanceInterface $referenceSourceElement,
        ?string $oldIdentifier,
        string $newIdentifier
    ): void;

    public function referencedElementUpdated(
        ModelElementInstanceInterface $referenceTargetElement,
        ?string $oldIdentifier,
        string $newIdentifier
    ): void {
        foreach ($this->findReferenceSourceElements($referenceTargetElement) as $referenceSourceElement) {
            $this->updateReference($referenceSourceElement, $oldIdentifier, $newIdentifier);
        }
    }

    abstract protected function removeReference(
        ModelElementInstanceInterface $referenceSourceElement,
        ModelElementInstanceInterface $referenceTargetElement
    ): void;

    /**
     * @param ModelElementInstanceInterface $referenceTargetElement
     * @param mixed $referenceIdentifier
     */
    public function referencedElementRemoved(
        ModelElementInstanceInterface $referenceTargetElement,
        $referenceIdentifier
    ): void {
        foreach ($this->findReferenceSourceElements($referenceTargetElement) as $referenceSourceElement) {
            if ($referenceIdentifier == $this->getReferenceIdentifier($referenceSourceElement)) {
                $this->removeReference($referenceSourceElement, $referenceTargetElement);
            }
        }
    }
}
