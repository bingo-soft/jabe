<?php

namespace BpmPlatform\Model\Xml\Type\Attribute;

use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\Reference\{
    AttributeReferenceBuilderInterface,
    AttributeReferenceCollectionInterface,
    AttributeReferenceCollectionBuilderInterface
};

interface StringAttributeBuilderInterface extends AttributeBuilderInterface
{
    public function namespace(string $namespaceUri): StringAttributeBuilderInterface;

    /**
     * @param mixed $defaultValue
     */
    public function defaultValue($defaultValue): StringAttributeBuilderInterface;

    public function required(): StringAttributeBuilderInterface;

    public function idAttribute(): StringAttributeBuilderInterface;

    public function qNameAttributeReference(string $referenceTargetElement): AttributeReferenceBuilderInterface;

    public function idAttributeReference(string $referenceTargetElement): AttributeReferenceBuilderInterface;

    public function idAttributeReferenceCollection(
        string $referenceTargetElement,
        string $attributeReferenceCollection
    ): AttributeReferenceCollectionBuilderInterface;
}
