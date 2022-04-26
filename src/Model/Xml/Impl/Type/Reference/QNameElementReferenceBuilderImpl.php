<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Impl\Type\Child\ChildElementImpl;

class QNameElementReferenceBuilderImpl extends ElementReferenceBuilderImpl
{
    public function __construct(
        string $childElementType,
        string $referenceTargetClass,
        ChildElementImpl $child
    ) {
        parent::__construct($childElementType, $referenceTargetClass, $child);
        $this->elementReferenceCollectionImpl = new QNameElementReferenceImpl($child);
    }
}
