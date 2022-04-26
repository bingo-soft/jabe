<?php

namespace Jabe\Model\Xml\Impl\Type\Attribute;

use Jabe\Model\Xml\Impl\Type\ModelElementTypeImpl;

class EnumAttributeBuilder extends AttributeBuilderImpl
{
    public function __construct(string $attributeName, ModelElementTypeImpl $modelType, string $type)
    {
        parent::__construct($attributeName, $modelType, new EnumAttribute($modelType, $type));
    }

    public function namespace(string $namespaceUri): EnumAttributeBuilder
    {
        parent::namespace($namespaceUri);
        return $this;
    }

    /**
     * @param mixed $defaultValue
     */
    public function defaultValue($defaultValue): EnumAttributeBuilder
    {
        parent::defaultValue($defaultValue);
        return $this;
    }

    public function required(): EnumAttributeBuilder
    {
        parent::required();
        return $this;
    }

    public function idAttribute(): EnumAttributeBuilder
    {
        parent::idAttribute();
        return $this;
    }
}
