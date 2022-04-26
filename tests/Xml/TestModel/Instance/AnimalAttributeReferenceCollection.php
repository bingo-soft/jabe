<?php

namespace Tests\Xml\TestModel\Instance;

use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Type\Attribute\AttributeImpl;
use Jabe\Model\Xml\Type\Reference\AttributeReferenceCollection;

class AnimalAttributeReferenceCollection extends AttributeReferenceCollection
{
    public function __construct(AttributeImpl $referenceSourceAttribute)
    {
        parent::__construct($referenceSourceAttribute);
    }

    protected function getTargetElementIdentifier(ModelElementInstanceInterface $referenceTargetElement): string
    {
        return $referenceTargetElement->getId();
    }
}
