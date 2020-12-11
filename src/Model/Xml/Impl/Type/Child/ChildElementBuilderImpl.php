<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Child;

use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;
use BpmPlatform\Model\Xml\Impl\Type\Reference\{
    QNameElementReferenceBuilderImpl,
    ElementReferenceBuilderImpl,
    UriElementReferenceBuilderImpl
};
use BpmPlatform\Model\Xml\Type\Child\{
    ChildElementBuilderInterface,
    ChildElementInterface
};
use BpmPlatform\Model\Xml\Type\Reference\ElementReferenceBuilderInterface;

class ChildElementBuilderImpl extends ChildElementCollectionBuilderImpl implements ChildElementBuilderInterface
{
    public function __construct(string $childElementTypeClass, ModelElementTypeInterface $parentElementType)
    {
        parent::__construct($childElementTypeClass, $parentElementType);
    }

    protected function createCollectionInstance(): ChildElementCollectionImpl
    {
        return new ChildElementImpl($this->childElementType, $this->parentElementType);
    }

    public function immutable(): ChildElementBuilderInterface
    {
        parent::immutable();
        return $this;
    }

    public function required(): ChildElementBuilderInterface
    {
        parent::required();
        return $this;
    }

    public function minOccurs(int $i): ChildElementBuilderInterface
    {
        parent::minOccurs($i);
        return $this;
    }

    public function maxOccurs(int $i): ChildElementBuilderInterface
    {
        parent::maxOccurs($i);
        return $this;
    }

    public function build(): ChildElementInterface
    {
        return parent::build();
    }

    public function qNameElementReference(
        string $referenceTargetType
    ): ElementReferenceBuilderInterface {
        $child = $this->build();
        $builder = new QNameElementReferenceBuilderImpl(
            $this->childElementType,
            $referenceTargetType,
            $child
        );
        $this->setReferenceBuilder($builder);
        return $builder;
    }

    public function idElementReference(
        string $referenceTargetType
    ): ElementReferenceBuilderInterface {
        $child = $this->build();
        $builder = new ElementReferenceBuilderImpl(
            $this->childElementType,
            $referenceTargetType,
            $collection
        );
        $this->setReferenceBuilder($builder);
        return $child;
    }

    public function uriElementReference(
        string $referenceTargetType
    ): ElementReferenceBuilderInterface {
        $child = $this->build();
        $builder = new UriElementReferenceBuilderImpl(
            $this->childElementType,
            $referenceTargetType,
            $collection
        );
        $this->setReferenceBuilder($builder);
        return $child;
    }
}
