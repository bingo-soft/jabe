<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Attribute;

use BpmPlatform\Model\Xml\Impl\Type\ModelElementTypeImpl;

class DoubleAttributeBuilder extends AttributeBuilderImpl
{
    public function __construct(string $attributeName, ModelElementTypeImpl $modelType)
    {
        parent::__construct($attributeName, $modelType, new DoubleAttribute($modelType));
    }

    public function namespace(string $namespaceUri): DoubleAttributeBuilder
    {
        parent::namespace($namespaceUri);
        return $this;
    }

    public function defaultValue(float $defaultValue): DoubleAttributeBuilder
    {
        parent::defaultValue($defaultValue);
        return $this;
    }

    public function required(): DoubleAttributeBuilder
    {
        parent::required();
        return $this;
    }

    public function idAttribute(): DoubleAttributeBuilder
    {
        parent::idAttribute();
        return $this;
    }
}
