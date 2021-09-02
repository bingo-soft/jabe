<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Attribute;

use BpmPlatform\Model\Xml\Impl\Type\ModelElementTypeImpl;

class IntegerAttributeBuilder extends AttributeBuilderImpl
{
    public function __construct(string $attributeName, ModelElementTypeImpl $modelType)
    {
        parent::__construct($attributeName, $modelType, new IntegerAttribute($modelType));
    }

    public function namespace(string $namespaceUri): IntegerAttributeBuilder
    {
        parent::namespace($namespaceUri);
        return $this;
    }

    /**
     * @param mixed $defaultValue
     */
    public function defaultValue($defaultValue): IntegerAttributeBuilder
    {
        parent::defaultValue($defaultValue);
        return $this;
    }

    public function required(): IntegerAttributeBuilder
    {
        parent::required();
        return $this;
    }

    public function idAttribute(): IntegerAttributeBuilder
    {
        parent::idAttribute();
        return $this;
    }
}
