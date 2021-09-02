<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Reference;

use BpmPlatform\Model\Xml\Impl\Type\Child\ChildElementImpl;
use BpmPlatform\Model\Xml\Type\Reference\{
    ElementReferenceInterface,
    ElementReferenceBuilderInterface
};

class ElementReferenceBuilderImpl extends ElementReferenceCollectionBuilderImpl implements ElementReferenceBuilderInterface
{
    public function __construct(
        string $childElementType,
        string $referenceTargetClass,
        ChildElementImpl $child
    ) {
        parent::__construct($childElementType, $referenceTargetClass, $child);
        $this->elementReferenceCollectionImpl = new ElementReferenceImpl($child);
    }

    public function build(): ElementReferenceInterface
    {
        return $this->elementReferenceCollectionImpl;
    }
}
