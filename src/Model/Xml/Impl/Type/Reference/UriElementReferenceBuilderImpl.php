<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Type\Child\ChildElementInterface;

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
