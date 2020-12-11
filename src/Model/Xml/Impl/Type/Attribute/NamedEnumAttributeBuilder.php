<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Attribute;

use BpmPlatform\Model\Xml\Impl\Type\ModelElementTypeImpl;

class NamedEnumAttributeBuilder extends AttributeBuilderImpl
{
    public function __construct(string $attributeName, ModelElementTypeImpl $modelType, string $type)
    {
        parent::__construct($attributeName, $modelType, new NamedEnumAttribute($modelType, $type));
    }

    public function namespace(string $namespaceUri): NamedEnumAttributeBuilder
    {
        parent::namespace($namespaceUri);
        return $this;
    }

    /**
     * @param mixed $defaultValue
     */
    public function defaultValue($defaultValue): NamedEnumAttributeBuilder
    {
        parent::defaultValue($defaultValue);
        return $this;
    }

    public function required(): NamedEnumAttributeBuilder
    {
        parent::required();
        return $this;
    }

    public function idAttribute(): NamedEnumAttributeBuilder
    {
        parent::idAttribute();
        return $this;
    }
}
