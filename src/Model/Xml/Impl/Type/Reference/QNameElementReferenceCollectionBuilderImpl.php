<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Impl\Type\Child\ChildElementCollectionImpl;

class QNameElementReferenceCollectionBuilderImpl extends ElementReferenceCollectionBuilderImpl
{
    public function __construct(
        string $childElementType,
        string $referenceTargetClass,
        ChildElementCollectionImpl $collection
    ) {
        parent::__construct($childElementType, $referenceTargetClass, $collection);
        $this->elementReferenceCollectionImpl = new QNameElementReferenceCollectionImpl($collection);
    }
}
