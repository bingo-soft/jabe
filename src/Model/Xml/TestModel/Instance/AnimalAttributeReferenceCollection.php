<?php

namespace BpmPlatform\Model\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\Impl\Type\Attribute\AttributeImpl;
use BpmPlatform\Model\Xml\Type\Reference\AttributeReferenceCollection;

class AnimalAttributeReferenceCollection extends AttributeReferenceCollection
{
    public function __construct(AttributeImpl $referenceSourceAttribute)
    {
        parent::__construct($referenceSourceAttribute);
    }

    protected function getTargetElementIdentifier(Animal $referenceTargetElement): string
    {
        return $referenceTargetElement->getId();
    }
}
