<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Reference;

use BpmPlatform\Model\Xml\Impl\Type\Attribute\AttributeImpl;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;
use BpmPlatform\Model\Xml\Type\Attribute\AttributeInterface;
use BpmPlatform\Model\Xml\Type\Reference\AttributeReferenceInterface;

class AttributeReferenceImpl extends ReferenceImpl implements AttributeReferenceInterface
{
    protected $referenceSourceAttribute;

    public function __construct(AttributeImpl $referenceSourceAttribute)
    {
        $this->referenceSourceAttribute = $referenceSourceAttribute;
    }

    public function getReferenceIdentifier(ModelElementInstanceInterface $referenceSourceElement): string
    {
        return $this->referenceSourceAttribute->getValue($referenceSourceElement);
    }

    protected function setReferenceIdentifier(
        ModelElementInstanceInterface $referenceSourceElement,
        string $referenceIdentifier
    ): void {
        $this->referenceSourceAttribute->setValue($referenceSourceElement, $referenceIdentifier);
    }

    public function getReferenceSourceAttribute(): AttributeInterface
    {
        return $this->referenceSourceAttribute;
    }

    public function getReferenceSourceElementType(): ModelElementTypeInterface
    {
        return $this->referenceSourceAttribute->getOwningElementType();
    }

    protected function updateReference(
        ModelElementInstanceInterface $referenceSourceElement,
        ?string $oldIdentifier,
        string $newIdentifier
    ): void {
        $referencingAttributeValue = $this->getReferenceIdentifier($referenceSourceElement);
        if ($oldIdentifier != null && $oldIdentifier == $referencingAttributeValue) {
            $this->setReferenceIdentifier($referenceSourceElement, $newIdentifier);
        }
    }

    protected function removeReference(
        ModelElementInstanceInterface $referenceSourceElement,
        ModelElementInstanceInterface $referenceTargetElement
    ): void {
        $this->referenceSourceAttribute->removeAttribute($referenceSourceElement);
    }
}
