<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Impl\Type\Attribute\AttributeImpl;

class QNameAttributeReferenceBuilderImpl extends AttributeReferenceBuilderImpl
{
    public function __construct(AttributeImpl $referenceSourceAttribute, string $referenceTargetElement)
    {
        parent::__construct($referenceSourceAttribute, $referenceTargetElement);
        $this->attributeReferenceImpl = new QNameAttributeReferenceImpl($referenceSourceAttribute);
    }
}
