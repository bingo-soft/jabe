<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Reference;

use BpmPlatform\Model\Xml\Type\Child\ChildElementInterface;

class UriElementReferenceBuilderImpl extends ElementReferenceBuilderImpl
{
    public function __construct(
        string $childElementType,
        string $referenceTargetClass,
        ChildElementInterface $child
    ) {
        parent::__construct($childElementType, $referenceTargetClass, $child);
        $this->elementReferenceCollectionImpl = new UriElementReferenceImpl($child);
    }
}
