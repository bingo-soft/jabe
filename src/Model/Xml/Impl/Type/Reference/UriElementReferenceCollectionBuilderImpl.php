<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Reference;

use BpmPlatform\Model\Xml\Type\Impl\Child\ChildElementCollectionImpl;

class UriElementReferenceBuilderImpl extends ElementReferenceBuilderImpl
{
    public function __construct(
        string $childElementType,
        string $referenceTargetClass,
        ChildElementCollectionImpl $collection
    ) {
        parent::__construct($childElementType, $referenceTargetClass, $collection);
        $this->elementReferenceCollectionImpl = new UriElementReferenceCollectionImpl($collection);
    }
}
